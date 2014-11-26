<?php
/* Copyright (C) 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../../include/anwesenheit.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(dirname(__FILE__).'/../config.inc.php');

class abrechnung extends basis_db
{
	public $new=true;
	public $result = array();
	
	public $abrechnung_id;		// serial
	public $mitarbeiter_uid;	// varchar(32)
	public $kostenstelle_id;	// integer
	public $konto_id;			// integer
	public $abrechnungsdatum;	// date
	public $sv_lfd;				// numeric(12,4)
	public $sv_satz;			// numeric(8,4)
	public $sv_teiler;			// integer
	public $honorar_dgf;		// numeric(12,4)
	public $honorar_offen;		// numeric(12,4)
	public $honorar_extra;		// numeric(12,4)
	public $brutto;				// numeric(12,4)
	public $netto;				// numeric(12,4)
	public $lst_lfd;			// numeric(12,4)
	public $abzuege; 			// numeric(12,4)
 
    /**
	 * Konstruktor
	 */
	public function __construct()
	{
	}

	/**
	 * Laedt die Mitarbeiter die noch offene Vertraege haben
	 */
	public function loadMitarbeiterUnabgerechnet()
	{
		$qry = "SELECT distinct
					vorname, nachname, uid, person_id, 
					(SELECT abrechnungsdatum 
					 FROM addon.tbl_abrechnung 
					 WHERE mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid 
					 ORDER BY abrechnungsdatum DESC LIMIT 1) as letzteabrechnung
				FROM 
					lehre.tbl_vertrag
					JOIN public.tbl_person USING(person_id)
					JOIN public.tbl_benutzer USING(person_id)
					JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
				WHERE
					NOT EXISTS (SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus 
								WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz='abgerechnet')";
	
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->uid = $row->uid;
				$obj->person_id = $row->person_id;
				$obj->letzteabrechnung =$row->letzteabrechnung;
				$this->result[] = $obj;
			}
			return true;
		}	
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert die letzte Abrechnung eines Mitarbeiters
	 * @param mitarbeiter_uid UID des Mitarbeiters
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getLetzteAbrechnung($mitarbeiter_uid)
	{
		$qry = "SELECT 
					* 
				FROM 
					addon.tbl_abrechnung 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." 
					AND kostenstelle_id is null
				ORDER BY 
					abrechnungsdatum desc LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->abrechnung_id = $row->abrechnung_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->kostenstelle_id = $row->kostenstelle_id;
				$this->konto_id = $row->konto_id;
				$this->abrechnungsdatum = $row->abrechnungsdatum;
				$this->sv_lfd = $row->sv_lfd;
				$this->sv_satz = $row->sv_satz;
				$this->sv_teiler = $row->sv_teiler;
				$this->honorar_dgf = $row->honorar_dgf;
				$this->honorar_offen = $row->honorar_offen;
				$this->honorar_extra = $row->honorar_extra;
				$this->brutto = $row->brutto;
				$this->netto = $row->netto;
				$this->lst_lfd = $row->lst_lfd;
				$this->abzuege = $row->abzuege;

				return true;
			}
			else
			{
				$this->errormsg='Bisher wurde keine Abrechnung durchgefuehrt';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Startet die Abrechnung eines Mitarbeiters
	 * Dabei werden die benötigten Zahlen berechnet und ein Log erstellt
	 * Die Daten werden dabei noch nicht gespeichert
	 * @param $username UID des Mitarbeiters
	 * @param $abrechnungsdatum Daten der Abrechnung
	 * @param $honorar_gesamt Gesamthonorar Brutto das Abgerechnet werden soll
	 * @param $verwendung_obj Verwendung die zur Abrechnung verwendet werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function abrechnung($username, $abrechnungsdatum, $honorar_gesamt, $verwendung_obj)
	{
		// Globale Variablen die im Abrechnungsconfig definiert sind
		global $cfg_sv_altersabschlag;
		global $cfg_sv_abschlaege;
		global $cfg_lsttgl;

		$this->log='';

		$this->mitarbeiter_uid = $username;
		$startdatum = $verwendung_obj->beginn;
		$endedatum = $verwendung_obj->ende;
		$this->dv_art = $verwendung_obj->dv_art;

		$dt_abrechnungsdatum = new DateTime($abrechnungsdatum);
		$this->honorar_gesamt = $honorar_gesamt;
		$this->abrechnungsdatum = $abrechnungsdatum;

		// Letzte Abrechnung laden
		$letzteabrechnung = new abrechnung();
		if($letzteabrechnung->getLetzteAbrechnung($username))
		{
			$this->letztesabrechnungsdatum = $letzteabrechnung->abrechnungsdatum;
			$honorar_durchgefuehrt = $letzteabrechnung->honorar_dgf+$letzteabrechnung->brutto;
		}
		else
		{
			$honorar_durchgefuehrt = 0;
			$this->letztesabrechnungsdatum = null;
		}

		// Gesamttage berechenen
		$this->tagegesamt = $this->BerechneGesamttage($startdatum, $endedatum);
		$this->log.="\nLetzte Abrechnung:".$this->letztesabrechnungsdatum;
		$this->log.="\nAbrechnungsdatum:".$abrechnungsdatum;
		$this->log.="\nTage gesamt:".$this->tagegesamt;

		// bereits ausbezahlte Tage berechnen
		if(!is_null($this->letztesabrechnungsdatum))
			$this->tageausbezahlt = $this->BerechneGesamttage($startdatum, $this->letztesabrechnungsdatum);
		else
			$this->tageausbezahlt = 0;
		$this->log.=' / Tage ausbezahlt:'.$this->tageausbezahlt;
		
		// noch abzurechnende Tage berechenen
		if(!is_null($this->letztesabrechnungsdatum))
			$this->tageabzurechnen = $this->BerechneGesamttage($this->letztesabrechnungsdatum, $abrechnungsdatum)-1;
		else
			$this->tageabzurechnen = $this->BerechneGesamttage($startdatum, $abrechnungsdatum);
		$this->log.=' / Tage abzurechnen:'.$this->tageabzurechnen;	

		// Offene Tage berechnen
		$this->tageoffen = $this->tagegesamt - $this->tageausbezahlt;
		$this->log.=' / Tage offen:'.$this->tageoffen;

		$honorar_offen = $honorar_gesamt - $honorar_durchgefuehrt;

		$this->honorar_dgf=$honorar_durchgefuehrt;
		$this->honorar_offen = $honorar_offen;

		$honorar_ausbezahlt = $honorar_durchgefuehrt + ($honorar_durchgefuehrt/6);
		$this->log.="\n\nHonorar ausbezahlt (lfd. Brutto kum. + Sonderzahlung kum.): ".number_format($honorar_ausbezahlt,2);
		$this->log.="\nHonorar durchgeführt: ".number_format($honorar_durchgefuehrt,2);
		$this->log.="\nHonorar offen: ".number_format($honorar_offen,2);
		$this->log.="\nSonderhonorar: im Gesamthonorar enthalten";
		$this->log.="\n----------------------------------------------";
		$this->log.="\nHonorar gesamt: ".number_format($honorar_gesamt,2);

		$this->brutto = ($this->honorar_gesamt - $honorar_ausbezahlt) / $this->tageoffen * $this->tageabzurechnen / 7 * 6;
		$this->log.="\n\n-> Lfd Brutto ((Honorar gesamt - bisher ausbezahlt) / Tage offen * Tage abzurechnen / 7 * 6): ".number_format($this->brutto,2);

		// Monatssechstel wird einbehalten und erst am Semesterende ausbezahlt
		$this->sonderzahlung = $this->brutto / 6;
		$this->log.="\n-> Sonderzahlung (Lfd Brutto / 6): ".number_format($this->sonderzahlung,2);

		$this->fiktivmonatsbezug = ($this->honorar_gesamt - $honorar_ausbezahlt) / $this->tageoffen * 30/7*6;
		$this->log.="\n-> Fiktivmonatsbezug ((Honorar gesamt - bisher ausbezahlt)/ Tage offen * 30 / 7 * 6): ".number_format($this->fiktivmonatsbezug,2);

		// SV Satz berechnen
		$this->sv_satz = SV_SATZ;
		$this->log.="\n\nSV-Satz: ".$this->sv_satz." - Abschläge";

		// Alter des Mitarbeiters berechenen
		$mitarbeiter = new mitarbeiter();
		if(!$mitarbeiter->load($username))
		{
			$this->errormsg = "User Load Failed:".$mitarbeiter->errormsg;
			return false;
		}

		$alter = date($abrechnungsdatum);
		$dt_geburtsdatum = new DateTime($mitarbeiter->gebdatum);
		$interval = $dt_abrechnungsdatum->diff($dt_geburtsdatum);
		$alter = $interval->format('d');

		// Altersabschlag	
		if($alter > $cfg_sv_altersabschlag[0])
		{
			$this->log.="\nAlter > ".$cfg_sv_altersabschlag[0].": -".$cfg_sv_altersabschlag[1];
			$this->sv_satz-=$cfg_sv_altersabschlag[1];
		}

		// Abschlaege
		foreach($cfg_sv_abschlaege as $abschlag)
		{
			if($this->fiktivmonatsbezug<$abschlag[0])
			{
				$this->log.="\nFiktivmonatsbezug < ".$abschlag[0].": -".$abschlag[1];
				$this->sv_satz-=$abschlag[1];
				break;
			}
		}

		// Wenn DV-Art=200 dann SV-Satz +0.05
		if($this->dv_art==200)
		{
			$this->log.="\nDV-Art=200 : +0.05";
			$this->sv_satz+=0.05;
		}

		if($this->fiktivmonatsbezug<SV_GERINGWERTIG)
		{
			$this->log.="\nFiktivmonatsbezug < ".SV_GERINGWERTIG.": SV-Satz=0";
			$this->sv_satz=0;
		}
		elseif(11==$dt_abrechnungsdatum->format('m'))
		{
			// Im November werden 10 Tage hinzugefuegt wenn nicht geringwertig
			$this->log.="\nAbrechnungsmonat=11 : Tage abzurechnen + 10";
			$this->tageabzurechnen+=10;
		}

		$this->sv_teiler = $this->tageabzurechnen;
		$this->log.="\n--------------------------------------------------";
		$this->log.="\n-> SV-Satz:".$this->sv_satz;
		$this->log.="\n";

		if($this->fiktivmonatsbezug>SV_HOECHSTBEMESSUNGSGRUNDLAGE)
		{
			$this->log.="\nFiktivmonatsbezug > ".SV_HOECHSTBEMESSUNGSGRUNDLAGE.": Fiktivmonatsbezug = ".SV_HOECHSTBEMESSUNGSGRUNDLAGE;
			$this->fiktivmonatsbezug = SV_HOECHSTBEMESSUNGSGRUNDLAGE;
		}

		$this->sv_lfd = $this->sv_satz * $this->fiktivmonatsbezug / 30 * $this->tageabzurechnen;
		$this->log.="\nLfd SV (SV-Satz * Fiktivmonatsbezug / 30 * Tage abzurechnen): ".number_format($this->sv_lfd,2);

		// BMGL Lohnsteuer
		$this->bmgllst = $this->brutto - $this->sv_lfd;
		$this->log.="\nBMGL Lst. (Lfd Brutto - Lfd SV): ".number_format($this->bmgllst,2);

		// BMGL Lohnsteuer taeglich
		$this->bmgllsttgl = $this->bmgllst / $this->tageabzurechnen;
		$this->log.="\nBMGL Lst. tgl. (BMGL Lst / Tage abzurechnen): ".number_format($this->bmgllsttgl,2);

		// LSt. taeglich:
		foreach($cfg_lsttgl as $row)
		{
			if($this->bmgllsttgl<=$row[0])
			{
				$this->log.="\nBMGL Lst. tgl. <=".$row[0]." -> ".$row[1];
				$this->lst_tgl = $row[1];
				break;
			}
		}
		$this->log.="\nLst. tgl.:".number_format($this->lst_tgl,2);

		// lfd. LSt.
		$this->lst_lfd = $this->lst_tgl * $this->tageabzurechnen;
		$this->log.="\nlfd. Lst. (Lst. tgl. * Tage anzurechnen): ".number_format($this->lst_lfd,2);

		// lfd. NETTO
		$this->netto = $this->bmgllst - $this->lst_lfd;
		$this->log.="\nlfd. Netto (BMGL Lst. - lfd. Lst.): ".number_format($this->netto,2);

		return true;
	}

	/**
	 * Berechnet die Gesamttage die ein Lektor angestellt ist
	 * ein Monat zaehlt fix 30 Tage
	 * Wenn ein Lektor am 31. angestellt wird, wird 1 Tag berechnet
	 */
	public function BerechneGesamtTage($startdatum, $endedatum)
	{
		$gesamttage=0;

		$datum = new DateTime($startdatum);
		$ende = new DateTime($endedatum);

		$i=0;
		while($datum<$ende)
		{
			$i++;
			if($i>100)
				die('Rekursion? Abbruch');

			$tag = $datum->format('d');
			if($tag==31)
				$gesamttage+=1;
			else
				$gesamttage+=31-$tag;

			$datum = new DateTime(date('Y-m-t',$datum->getTimestamp())); // Letzten Tag im Monat
			$datum->add(new DateInterval('P1D')); // 1 Tag dazuzaehlen
		}

		return $gesamttage;
	}

	/**
	 * Prueft ob eine Abrechnung bereits existiert
	 * @param $username
	 * @param $abrechnungsdatum
	 * @return boolean
	 */
	public function exists($username, $abrechnungsdatum)
	{
		$qry = "SELECT 
					1 
				FROM 
					addon.tbl_abrechnung 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($username)." 
					AND abrechnungsdatum=".$this->db_add_param($abrechnungsdatum);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
	}

	/**
	 * Laedt die Prozentuelle Aufteilung auf die einzelnen Kostenstellen
	 */
	public function loadVertragsAufteilung($vertrag_arr)
	{
		$this->aufteilung=array();

		$qry = "SELECT 
					sum(tbl_lehreinheitmitarbeiter.semesterstunden) as semesterstunden, lehrfach.oe_kurzbz,
					(SELECT kostenstelle_id FROM wawi.tbl_kostenstelle WHERE oe_kurzbz=lehrfach.oe_kurzbz AND aktiv LIMIT 1) as kostenstelle_id
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
				WHERE
					tbl_lehreinheitmitarbeiter.vertrag_id IN(".$this->db_implode4SQL($vertrag_arr).")
				GROUP BY lehrfach.oe_kurzbz";

		if($result = $this->db_query($qry))
		{
			$gesamtsemesterstunden=0;
			while($row = $this->db_fetch_object($result))
			{
				$this->aufteilung[$row->oe_kurzbz]['semesterstunden']=$row->semesterstunden;
				$this->aufteilung[$row->oe_kurzbz]['kostenstelle_id']=$row->kostenstelle_id;
				$gesamtsemesterstunden+=$row->semesterstunden;
			}

			foreach($this->aufteilung as $oe=>$row)
			{
				$anteil = $row['semesterstunden'] / $gesamtsemesterstunden * 100;
				$this->aufteilung[$oe]['prozent']=$anteil;
			}
		}
	}

	/**
	 * Speichert die Abrechnung in die Datenbank
	 * Vor dem Aufruf muss die Aufteilung auf die Kostenstellen berechnet werden
	 */
	public function saveAbrechnung()
	{
		$qry = "BEGIN;INSERT INTO addon.tbl_abrechnung(mitarbeiter_uid, kostenstelle_id, konto_id, abrechnungsdatum, 	
				sv_lfd, sv_satz, sv_teiler, honorar_dgf, honorar_offen, honorar_extra, brutto, netto, 
				lst_lfd, abzuege) VALUES(".
				$this->db_add_param($this->mitarbeiter_uid).',null, null,'.
				$this->db_add_param($this->abrechnungsdatum).','.
				$this->db_add_param($this->sv_lfd).','.
				$this->db_add_param($this->sv_satz).','.
				$this->db_add_param($this->sv_teiler).','.
				$this->db_add_param($this->honorar_dgf).','.
				$this->db_add_param($this->honorar_offen).','.
				$this->db_add_param($this->honorar_extra).','.
				$this->db_add_param($this->brutto).','.
				$this->db_add_param($this->netto).','.
				$this->db_add_param($this->lst_lfd).','.
				$this->db_add_param($this->abzuege).');';

		foreach($this->aufteilung as $row)
		{
			$prozent = $row['prozent'];

			$qry.= "INSERT INTO addon.tbl_abrechnung(mitarbeiter_uid, kostenstelle_id, konto_id, abrechnungsdatum, 	
				sv_lfd, sv_satz, sv_teiler, honorar_dgf, honorar_offen, honorar_extra, brutto, netto, 
				lst_lfd, abzuege) VALUES(".
				$this->db_add_param($this->mitarbeiter_uid).','.
				$this->db_add_param($row['kostenstelle_id']).','.
				$this->db_add_param($this->konto_id).','.
				$this->db_add_param($this->abrechnungsdatum).','.
				$this->db_add_param($this->sv_lfd).','.
				$this->db_add_param($this->sv_satz).','.
				$this->db_add_param($this->sv_teiler).','.
				$this->db_add_param($this->honorar_dgf).','.
				$this->db_add_param($this->honorar_offen).','.
				$this->db_add_param($this->honorar_extra).','.
				$this->db_add_param($this->brutto/100*$prozent).','.
				$this->db_add_param($this->netto/100*$prozent).','.
				$this->db_add_param($this->lst_lfd/100*$prozent).','.
				$this->db_add_param($this->abzuege/100*$prozent).');';
		}

		$qry.="COMMIT;";
		//TODO Betrag auf Mitarbeiterkonto Buchen

		if($this->db_query($qry))
			return true;
		else
			return false;
	}

	/**
	 * Laedt die Abrechnungen eines Mitarbeiters in einem Studiensemester
	 * @param mitarbeiter_uid UID des Mitarbeiters
	 * @param studiensemester_kurzbz Studiensemester
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAbrechnungen($mitarbeiter_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					* 
				FROM 
					addon.tbl_abrechnung 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." 
					AND kostenstelle_id is null
					AND abrechnungsdatum>=(SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")
					AND abrechnungsdatum<=(SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{

				$obj = new stdClass();
			
				$obj->abrechnung_id = $row->abrechnung_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->kostenstelle_id = $row->kostenstelle_id;
				$obj->konto_id = $row->konto_id;
				$obj->abrechnungsdatum = $row->abrechnungsdatum;
				$obj->sv_lfd = $row->sv_lfd;
				$obj->sv_satz = $row->sv_satz;
				$obj->sv_teiler = $row->sv_teiler;
				$obj->honorar_dgf = $row->honorar_dgf;
				$obj->honorar_offen = $row->honorar_offen;
				$obj->honorar_extra = $row->honorar_extra;
				$obj->brutto = $row->brutto;
				$obj->netto = $row->netto;
				$obj->lst_lfd = $row->lst_lfd;
				$obj->abzuege = $row->abzuege;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function abschlussNoetig($mitarbeiter_uid, $abrechnungsdatum, $verwendung_obj)
	{
		$gesamttage = $this->BerechneGesamttage($verwendung_obj->beginn, $verwendung_obj->ende);
		$abgerechnet = $this->BerechneGesamttage($verwendung_obj->beginn, $abrechnungsdatum);
		$qry = "SELECT 
					* 
				FROM 
					addon.tbl_abrechnung 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum>=".$this->db_add_param($verwendung_obj->beginn)."
					AND abrechnungsdatum<=".$this->db_add_param($verwendung_obj->ende)."
					AND kostenstelle_id is null";

		$gesamttageabgerechnet=0;
		$abrechnungsdatumfound=0;
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				if($row->abrechnungsdatum==$abrechnungsdatum)
					$abrechnungsdatumfound++;
			}
		}

		// Wenn alle Tage abgerechnet wurde aber noch keine Endabrechnung vorhanden ist
		if($abrechnungsdatumfound==1 && ($gesamttage - $abgerechnet)==0)
			return true;
		else
			return false;
	}
	
	public function abschluss($mitarbeiter_uid, $abrechungsdatum, $honorar_gesamt, $verwendung_obj, $vertrag_arr)
	{
		$datum_obj = new datum();
		$this->log='';

		// Anwesenheiten ermitteln
		$qry = "SELECT 
					lehreinheit_id, tbl_lehrveranstaltung.bezeichnung 
				FROM 
					lehre.tbl_lehreinheitmitarbeiter 
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE vertrag_id in(".$this->implode4SQL($vertrag_arr).")";

		$anzahl_termine=0;
		$anzahl_anwesend=0;

		$anwesenheitslog='';
		$anwesenheitsabzug=0;

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))	
			{
				$anwesenheit = new anwesenheit();
				$anwesenheit->loadAnwesenheitMitarbeiter($mitarbeiter_uid, $row->lehreinheit_id);

				$anzahl_termine+=$anwesenheit->anzahl_termine;
				$anzahl_anwesend+=$anwesenheit->anzahl_anwesend;
				foreach($anwesenheit->anwesenheit as $row_aw)
				{
					if(!$row_aw['anwesend'])
					{
						$anwesenheitsabzug += $row_aw['einheiten']*$row_aw['stundensatz'];
						$anwesenheitslog.="\n".$row->bezeichnung.' am '.$datum_obj->formatDatum($row_aw['datum'],'d.m.Y').' - '.$row_aw['einheiten'].' Einheiten á € '.$row_aw['stundensatz'].' = € '.($row_aw['einheiten']*$row_aw['stundensatz']);
					}
				}
			}
		}

		$this->log.="\nAnwesenheit: $anzahl_anwesend/$anzahl_termine = ".number_format(($anzahl_anwesend/$anzahl_termine*100),2)." % ";
		$this->log.="\n".$anwesenheitslog."\n";

		// Berechnung der Zurueckgehaltenen Sonderzahlungen
		$qry = "SELECT 
					* 
				FROM 
					addon.tbl_abrechnung 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum>=".$this->db_add_param($verwendung_obj->beginn)."
					AND abrechnungsdatum<=".$this->db_add_param($verwendung_obj->ende)."
					AND kostenstelle_id is null";

		$gesamttageabgerechnet=0;
		$abrechnungsdatumfound=0;
		$honorar_offen=0;
		$honorar_ausbezahlt=0;
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$honorar_ausbezahlt += $row->brutto;
			}
		}

		$honorar_offen = $honorar_gesamt - $honorar_ausbezahlt;

		$this->log.="\nHonorar gesamt: ".number_format($honorar_gesamt,2);
		$this->log.="\nHonorar ausbezahlt: ".number_format($honorar_ausbezahlt,2);
		$this->log.="\nHonorar offen: ".number_format($honorar_offen,2);
		$this->log.="\nAbzug für nicht anwesende Stunden: ".number_format($anwesenheitsabzug,2);
		$this->log.="\n---------------------------------";
		$honorar_offen = $honorar_offen - $anwesenheitsabzug;
		$this->log.="\nSaldo: ".number_format($honorar_offen,2);
	}
}
?>
