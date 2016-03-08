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
/**
 * Klasse zur Berechnung der monatlichen Honorars von freien Lektoren
 *
 */
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../../include/anwesenheit.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../../include/wawi_konto.class.php');
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
	public $brutto;				// numeric(12,4)
	public $netto;				// numeric(12,4)
	public $lst_lfd;			// numeric(12,4)
	public $abschluss=false;	// boolean
	public $log;				// text
	public $tagegesamt;
	public $tageabzurechnen;
	public $tageausbezahlt;
	public $honorar_gesamt;
	public $bmgllsttgl;
	public $fiktivmonatsbezug;
	public $importiert=false;

    /**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
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
					tbl_mitarbeiter.fixangestellt = false
					AND NOT EXISTS (SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus
								WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz in ('abgerechnet','storno'))
				ORDER BY nachname, vorname";

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
	 * Laedt die Abrechnung eines Mitarbeiters zu einem Abrechnungsdatum
	 * @param mitarbeiter_uid UID des Mitarbeiters
	 * @param abrechnugsdatum
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAbrechnungMitarbeiter($mitarbeiter_uid, $abrechnungsdatum)
	{
		$qry = "SELECT
					*
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum=".$this->db_add_param($abrechnungsdatum)."
					AND kostenstelle_id is null
					AND abschluss=false
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
				$this->brutto = $row->brutto;
				$this->netto = $row->netto;
				$this->lst_lfd = $row->lst_lfd;
				$this->log = $row->log;
				$this->importiert = $this->db_parse_bool($row->importiert);

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
	 * Liefert die letzte Abrechnung eines Mitarbeiters
	 * @param mitarbeiter_uid UID des Mitarbeiters
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getLetzteAbrechnung($mitarbeiter_uid, $startdatum=null)
	{
		$qry = "SELECT
					*
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND kostenstelle_id is null";
		if(!is_null($startdatum))
			$qry.=" AND abrechnungsdatum>=".$this->db_add_param($startdatum);

		$qry.="	ORDER BY
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
				$this->brutto = $row->brutto;
				$this->netto = $row->netto;
				$this->lst_lfd = $row->lst_lfd;
				$this->log = $row->log;

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
	 * @param $vertrag_arr Array mit den VertragsIDs die abgerechnet werden
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function abrechnung($username, $abrechnungsdatum, $verwendung_obj)
	{
		// Globale Variablen die im Abrechnungsconfig definiert sind
		global $cfg_sv_altersabschlag;
		global $cfg_sv_abschlaege;
		global $cfg_lsttgl;

		$datum_obj = new datum();

		$this->log='';

		$mitarbeiter = new mitarbeiter();
		if(!$mitarbeiter->load($username))
		{
			$this->errosmg = 'Fehler beim Laden des Mitabreiters';
			return false;
		}

		$vertrag = new vertrag();
		$vertrag->loadVertrag($mitarbeiter->person_id, false);
		$gesamtbetrag=0;
		$this->vertrag_arr=array();
		foreach($vertrag->result as $row)
		{
			$this->log.="\n -> ".$row->bezeichnung.' - € '.$row->betrag.' (ID '.$row->vertrag_id.')';
			$gesamtbetrag+=$row->betrag;
			$this->vertrag_arr[]=$row->vertrag_id;
		}
		$this->log.="\n";

		$this->mitarbeiter_uid = $username;
		$startdatum = $verwendung_obj->beginn;
		$endedatum = $verwendung_obj->ende;
		$this->dv_art = $verwendung_obj->dv_art;

		$dt_abrechnungsdatum = new DateTime($abrechnungsdatum);
		$this->honorar_gesamt = $gesamtbetrag;
		$this->abrechnungsdatum = $abrechnungsdatum;

		// Letzte Abrechnung laden
		$letzteabrechnung = new abrechnung();
		if($letzteabrechnung->getLetzteAbrechnung($username, $startdatum))
		{
			$this->letztesabrechnungsdatum = $letzteabrechnung->abrechnungsdatum;
			//$honorar_durchgefuehrt = $letzteabrechnung->honorar_dgf+$letzteabrechnung->brutto;
		}
		else
		{
			//$honorar_durchgefuehrt = 0;
			$this->letztesabrechnungsdatum = null;
		}

		$honorar_durchgefuehrt = $this->getAbrechnungsbrutto($username, $startdatum, $endedatum);

		// Gesamttage berechenen
		$this->tagegesamt = $this->BerechneGesamttage($startdatum, $endedatum, $endedatum);
		$this->log.="\nLetzte Abrechnung: ".$datum_obj->formatDatum($this->letztesabrechnungsdatum, 'd.m.Y');
		$this->log.="\nAbrechnungsdatum: ".$datum_obj->formatDatum($abrechnungsdatum, 'd.m.Y');
		$this->log.="\nTage gesamt: ".$this->tagegesamt;

		// bereits ausbezahlte Tage berechnen
		if(!is_null($this->letztesabrechnungsdatum))
			$this->tageausbezahlt = $this->BerechneGesamttage($startdatum, $this->letztesabrechnungsdatum, $endedatum);
		else
			$this->tageausbezahlt = 0;
		$this->log.=' / Tage ausbezahlt:'.$this->tageausbezahlt;

		// noch abzurechnende Tage berechenen
		if(!is_null($this->letztesabrechnungsdatum))
		{
			// 1 Tag dazuzaehlen da ab dem 1. gerechnet wird
			$letztesabrechnungsdatumplus1 = new DateTime($this->letztesabrechnungsdatum);
			$letztesabrechnungsdatumplus1->add(new DateInterval('P1D'));

			$this->tageabzurechnen = min(array($this->BerechneGesamttage($letztesabrechnungsdatumplus1->format('Y-m-d'), $abrechnungsdatum, $endedatum),30));
		}
		else
			$this->tageabzurechnen = $this->BerechneGesamttage($startdatum, $abrechnungsdatum, $endedatum);
		$this->log.=' / Tage abzurechnen:'.$this->tageabzurechnen;

		// Offene Tage berechnen
		$this->tageoffen = $this->tagegesamt - $this->tageausbezahlt;
		$this->log.=' / Tage offen (nach dieser Abrechnung):'.($this->tageoffen-$this->tageabzurechnen);

		$honorar_offen = $this->honorar_gesamt - $honorar_durchgefuehrt;

		$this->honorar_dgf=$honorar_durchgefuehrt;
		$this->honorar_offen = $honorar_offen;

		$honorar_ausbezahlt = $honorar_durchgefuehrt + ($honorar_durchgefuehrt/6);
		$this->log.="\n\nHonorar abgerechnet (lfd. Brutto kum. + Sonderzahlung kum.): ".number_format($honorar_ausbezahlt,2);
		$this->log.="\nHonorar ausbezahlt brutto: ".number_format($honorar_durchgefuehrt,2);
		$this->log.="\nHonorar offen: ".number_format($honorar_offen,2);
		$this->log.="\nSonderhonorar: im Gesamthonorar enthalten";
		$this->log.="\n----------------------------------------------";
		$this->log.="\nHonorar gesamt: ".number_format($this->honorar_gesamt,2);

		$this->log.="\n\nAbzug für nicht gehaltene Stunden:";
		// Honorar anziehen das bis zu diesem Zeitpunkt nicht gehalten wurde
		$abzug = $this->loadAnwesenheitsabzug($username, $this->vertrag_arr, $abrechnungsdatum);
		if($abzug==0)
			$this->log.="\n-> Kein Abzug für Fehlstunden in diesem Zeitraum.";
		$this->honorar_gesamt -= $abzug;

		$this->log.="\nHonorar gesamt nach Abzügen: ".number_format($this->honorar_gesamt,2);

		if($this->tageoffen==0)
			$this->log.="\n\n Tage offen=0 -> Berechnung des Bruttobetrag nicht möglich!";
		else
			$this->brutto = ($this->honorar_gesamt - $honorar_ausbezahlt) / $this->tageoffen * $this->tageabzurechnen / 7 * 6;
		$this->log.="\n\n-> Lfd Brutto ((Honorar gesamt - bisher ausbezahlt) / Tage offen * Tage abzurechnen / 7 * 6): ".number_format($this->brutto,2);

		// Monatssechstel wird einbehalten und erst am Semesterende ausbezahlt
		$this->sonderzahlung = $this->brutto / 6;
		$this->log.="\n-> Sonderzahlung (Lfd Brutto / 6): ".number_format($this->sonderzahlung,2);

		if($this->tageoffen==0)
			$this->log.="\n\n Tage offen=0 -> Berechnung des fiktivmonatsbezug nicht möglich!";
		else
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
		if($this->tageabzurechnen!=0)
		{
			$this->bmgllsttgl = $this->bmgllst / $this->tageabzurechnen;
			$this->log.="\nBMGL Lst. tgl. (BMGL Lst / Tage abzurechnen): ".number_format($this->bmgllsttgl,2);
		}
		else
			$this->log.="\nTage abzurechnen = 0!! BMGL Lst. tgl. kann nicht berechnet werden";

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
	 * Das erste Monat wird immer bis zum tatsaechlichen Monatsende gerechnet.
	 *
	 * @param $startdatum Beginndatum
	 * @param $endedatum Endedatum
	 * @param $vertragsendedatum Datum des Vertragsendes
	 * @return $gesamttage Anzahl der Tage
	 */
	public function BerechneGesamtTage($startdatum, $endedatum, $vertragsendedatum)
	{
		$gesamttage=0;

		$datum = new DateTime($startdatum);
		$ende = new DateTime($endedatum);

		$dtstartdatum = new DateTime($startdatum);
		$dtendedatum = new DateTime($endedatum);
		$dtvertragsendedatum = new DateTime($vertragsendedatum);

		// Wenn das Ende des Vertrags erreicht wurde nur bis zu diesem Rechnen
		if($dtendedatum>$dtvertragsendedatum)
		{
			$ende = $dtvertragsendedatum;
			$dtendedatum = $dtvertragsendedatum;
		}

		$i=0;

		// Wenn die Verwendung am letzten Tag des Monats startet wird 1 Tag abgerechnet
		// zB am 30.9. oder 31.8.
		if($dtstartdatum==$dtendedatum)
			return 1;

		while($datum<$ende || $datum->format('d')==31)
		{
			// Tag des letzten Tages im Monat ermitteln
			$letzterTagimMonat = new DateTime(date('Y-m-t',$datum->getTimestamp()));
			$letzterTagimMonat = $letzterTagimMonat->format('d');

			$i++;
			if($i>100)
				die('Abbruch beim Berechnen der Tage aufgrund Endlosschleife: Start:'.$startdatum.', Ende:'.$endedatum.', Vertragsende:'.$vertragsendedatum);

			$tag = $datum->format('d');

			if($dtstartdatum->format('m')==$datum->format('m')  && $dtvertragsendedatum->format('m')!=$datum->format('m'))
			{
				// 1. Monat
				// hier wird immer bis zum tatsaechlichen Monatsende gerechnet nicht bis zum 30. zB im Feb. bis 28.
				$gesamttage+=$letzterTagimMonat-$tag+1;
			}
			elseif($dtvertragsendedatum->format('m')==$datum->format('m') && $dtendedatum->format('m')==$datum->format('m'))
			{
				// Letzter Monat
				// hier werden einfach die Tage des Monats dazugerechnet
				// Das trifft aber nur zu wenn es das Monat des Vertragsendes ist. Ansonsten wird das volle Monat gerechnet
				// da sonst die berechnung der bereits abgerechneten Tage nicht korrekt ist.
				if($dtendedatum->format('d')>30)
					$gesamttage+=30;
				else
					$gesamttage+=$dtendedatum->format('d');
			}
			else
			{
				// Zwischenmonat
				// hier wird immer mit 30 Tagen gerechnet
				$gesamttage+=30;
			}

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
	 * Laedt die Aufteilung auf die einzelnen Kostenstellen
	 * Auf die Kostenstellen wird nicht der ausbezahlt Betrag gebucht sondern der Anteil der
	 * bereits gehalten wurden. Sonderhonorare werden zur gaenze in betreffenden Monat verbucht
	 */
	public function loadVertragsAufteilung()
	{
		$this->aufteilung=array();

		$qry = "SELECT sum(betrag) as betrag, oe_kurzbz, kostenstelle_id FROM
				(
				SELECT
					sum(betrag) as betrag, tbl_studiengang.oe_kurzbz,
					(SELECT kostenstelle_id FROM addon.tbl_abrechnung_kostenstelle WHERE studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND (sprache=(SELECT distinct sprache FROM lehre.tbl_studienplan JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id) where lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND sprache is not null LIMIT 1) OR sprache is null) AND (orgform_kurzbz=tbl_lehrveranstaltung.orgform_kurzbz OR orgform_kurzbz is null) ORDER BY sprache NULLS LAST, orgform_kurzbz NULLS LAST limit 1) as kostenstelle_id
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
					JOIN lehre.tbl_vertrag ON(tbl_lehreinheitmitarbeiter.vertrag_id=tbl_vertrag.vertrag_id)
				WHERE
					tbl_lehreinheitmitarbeiter.vertrag_id IN(".$this->db_implode4SQL($this->vertrag_arr).")
				GROUP BY tbl_studiengang.oe_kurzbz, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.orgform_kurzbz, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id
				UNION ALL
				SELECT
					sum(betrag) as betrag, tbl_studiengang.oe_kurzbz,
					(SELECT kostenstelle_id FROM addon.tbl_abrechnung_kostenstelle WHERE studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND (sprache=(SELECT distinct sprache FROM lehre.tbl_studienplan JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id) where lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND sprache is not null LIMIT 1) OR sprache is null) AND (orgform_kurzbz=tbl_lehrveranstaltung.orgform_kurzbz OR orgform_kurzbz is null) ORDER BY sprache NULLS LAST, orgform_kurzbz NULLS LAST limit 1) as kostenstelle_id
				FROM
					lehre.tbl_vertrag
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					vertrag_id IN(".$this->db_implode4SQL($this->vertrag_arr).")
				GROUP BY tbl_studiengang.oe_kurzbz, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.orgform_kurzbz, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id
				) a group by oe_kurzbz, a.kostenstelle_id";

		if($result = $this->db_query($qry))
		{
			$gesamtbetrag=0;
			while($row = $this->db_fetch_object($result))
			{
				if($row->kostenstelle_id!='')
				{
					// Wenn 2 oe_kurzbz auf die selbe Kostentelle abgerechnet werden
					if(isset($this->aufteilung[$row->kostenstelle_id]['betrag']))
						$this->aufteilung[$row->kostenstelle_id]['betrag']+=$row->betrag;
					else
						$this->aufteilung[$row->kostenstelle_id]['betrag']=$row->betrag;
					$this->aufteilung[$row->kostenstelle_id]['kostenstelle_id']=$row->kostenstelle_id;
					$gesamtbetrag+=$row->betrag;
				}
			}

			if($gesamtbetrag==0)
				$gesamtbetrag=1;
			foreach($this->aufteilung as $kst=>$row)
			{
				$anteil = $row['betrag'] / $gesamtbetrag * 100;
				$this->aufteilung[$kst]['prozent']=$anteil;
			}
		}
	}

	/**
	 * Laedt das Konto eines Mitarbeiters
	 *
	 * @return true wenn erfolgreich
	 * @return false im Fehlerfall
	 */
	protected function loadMitarbeiterKonto()
	{
		$benutzer = new benutzer();
		$benutzer->load($this->mitarbeiter_uid);
		$konto = new wawi_konto();
		$konto->getKontoPerson($benutzer->person_id);
		if(isset($konto->result[0]))
		{
			$this->konto_id = $konto->result[0]->konto_id;
			return true;
		}
		else
		{
			$this->errormsg = 'Mitarbeiter hat noch kein Konto';
			return false;
		}
	}

	/**
	 * Speichert die Abrechnung in die Datenbank
	 * Vor dem Aufruf muss die Aufteilung auf die Kostenstellen berechnet werden
	 */
	public function saveAbrechnung()
	{
		if(!$this->loadMitarbeiterKonto())
			return false;

		$qry = "BEGIN;INSERT INTO addon.tbl_abrechnung(mitarbeiter_uid, kostenstelle_id, konto_id, abrechnungsdatum,
				sv_lfd, sv_satz, sv_teiler, honorar_dgf, honorar_offen, brutto, netto,
				lst_lfd, log, abschluss, importiert) VALUES(".
				$this->db_add_param($this->mitarbeiter_uid).',null,'.
				$this->db_add_param($this->konto_id).','.
				$this->db_add_param($this->abrechnungsdatum).','.
				$this->db_add_param($this->sv_lfd).','.
				$this->db_add_param($this->sv_satz).','.
				$this->db_add_param($this->sv_teiler).','.
				$this->db_add_param($this->honorar_dgf).','.
				$this->db_add_param($this->honorar_offen).','.
				$this->db_add_param($this->brutto).','.
				$this->db_add_param($this->netto).','.
				$this->db_add_param($this->lst_lfd).','.
				$this->db_add_param($this->log).','.
				$this->db_add_param($this->abschluss, FHC_BOOLEAN).','.
				$this->db_add_param($this->importiert, FHC_BOOLEAN).');';

		foreach($this->aufteilung as $row)
		{
			$prozent = $row['prozent'];

			$qry.= "INSERT INTO addon.tbl_abrechnung(mitarbeiter_uid, kostenstelle_id, konto_id, abrechnungsdatum,
				sv_lfd, sv_satz, sv_teiler, honorar_dgf, honorar_offen, brutto, netto, lst_lfd, abschluss) VALUES(".
				$this->db_add_param($this->mitarbeiter_uid).','.
				$this->db_add_param($row['kostenstelle_id']).','.
				$this->db_add_param($this->konto_id).','.
				$this->db_add_param($this->abrechnungsdatum).','.
				$this->db_add_param($this->sv_lfd).','.
				$this->db_add_param($this->sv_satz).','.
				$this->db_add_param($this->sv_teiler).','.
				$this->db_add_param($this->honorar_dgf).','.
				$this->db_add_param($this->honorar_offen).','.
				$this->db_add_param($this->brutto/100*$prozent).','.
				$this->db_add_param($this->netto/100*$prozent).','.
				$this->db_add_param($this->lst_lfd/100*$prozent).','.
				$this->db_add_param($this->abschluss, FHC_BOOLEAN).');';
		}

		$qry.="COMMIT;";
		//TODO Betrag auf Mitarbeiterkonto Buchen

		if($this->db_query($qry))
			return true;
		else
			return false;
	}


	/**
	 *
	 */
	public function save()
	{

		if($this->new)
		{
			$qry = "INSERT INTO addon.tbl_abrechnung(mitarbeiter_uid, kostenstelle_id, konto_id, abrechnungsdatum,
					sv_lfd, sv_satz, sv_teiler, honorar_dgf, honorar_offen, brutto, netto,
					lst_lfd, log, abschluss, importiert) VALUES(".
					$this->db_add_param($this->mitarbeiter_uid).',null,'.
					$this->db_add_param($this->konto_id).','.
					$this->db_add_param($this->abrechnungsdatum).','.
					$this->db_add_param($this->sv_lfd).','.
					$this->db_add_param($this->sv_satz).','.
					$this->db_add_param($this->sv_teiler).','.
					$this->db_add_param($this->honorar_dgf).','.
					$this->db_add_param($this->honorar_offen).','.
					$this->db_add_param($this->brutto).','.
					$this->db_add_param($this->netto).','.
					$this->db_add_param($this->lst_lfd).','.
					$this->db_add_param($this->log).','.
					$this->db_add_param($this->abschluss, FHC_BOOLEAN).','.
					$this->db_add_param($this->importiert, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = "UPDATE addon.tbl_abrechnung
					SET mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid).','.
					' kostenstelle_id='.$this->db_add_param($this->kostenstelle_id).','.
					' konto_id='.$this->db_add_param($this->konto_id).','.
					' abrechnungsdatum='.$this->db_add_param($this->abrechnungsdatum).','.
					' sv_lfd='.$this->db_add_param($this->sv_lfd).','.
					' sv_satz='.$this->db_add_param($this->sv_satz).','.
					' sv_teiler='.$this->db_add_param($this->sv_teiler).','.
					' honorar_dgf='.$this->db_add_param($this->honorar_dgf).','.
					' honorar_offen='.$this->db_add_param($this->honorar_offen).','.
					' brutto='.$this->db_add_param($this->brutto).','.
					' netto='.$this->db_add_param($this->netto).','.
					' lst_lfd='.$this->db_add_param($this->lst_lfd).','.
					' log='.$this->db_add_param($this->log).','.
					' abschluss='.$this->db_add_param($this->abschluss, FHC_BOOLEAN).', '.
					' importiert='.$this->db_add_param($this->importiert, FHC_BOOLEAN).' '.
					' WHERE abrechnung_id='.$this->db_add_param($this->abrechnung_id, FHC_INTEGER, false);
		}

		if($this->db_query($qry))
			return true;
		else
			return false;
	}

	/**
	 * Liefert die Brutto Summe
	 */
	public function getAbrechnungsbrutto($mitarbeiter_uid, $vertragsstart, $vertragsende)
	{
		$date = DateTime::createFromFormat('Y-m-d', $vertragsende);
		$monatsletzter = $date->format('Y-m-t');
		$qry = "SELECT
					sum(brutto) as brutto
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum>=".$this->db_add_param($vertragsstart)."
					AND abrechnungsdatum<=".$this->db_add_param($monatsletzter)."
					AND kostenstelle_id IS NULL
					AND abschluss=false";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->brutto;
			}
			else
			{
				return 0;
			}
		}
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
				$obj->brutto = $row->brutto;
				$obj->netto = $row->netto;
				$obj->lst_lfd = $row->lst_lfd;

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
	 * Prueft ob eine Abschlussabrechnung noetig ist
	 *
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 * @param $abrechnungsdatum Monat der Abrechnung
	 * @param $verwendung_obj
	 *
	 * @return true Abrechnung ist noetig
	 * @return false Abrechnung ist nicht noetig
	 */
	public function abschlussNoetig($mitarbeiter_uid, $abrechnungsdatum, $verwendung_obj)
	{
		$gesamttage = $this->BerechneGesamttage($verwendung_obj->beginn, $verwendung_obj->ende, $verwendung_obj->ende);
		$abgerechnet = $this->BerechneGesamttage($verwendung_obj->beginn, $abrechnungsdatum, $verwendung_obj->ende);
		$qry = "SELECT
					*
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum>=".$this->db_add_param($verwendung_obj->beginn)."
					AND abrechnungsdatum<=".$this->db_add_param($abrechnungsdatum)."
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

	/**
	 * Fuehrt eine Abschlussabrechnung durch
	 *
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 * @param $abrechnugnsdatum Monat der Abrechnung
	 * @param $honorar_gesamt Gesamthonorar des Lektors in diesem Semester
	 * @param $verwendung_obj
	 * @param $vertrag_arr Array mit IDs zu den Vertraegen dei abgerechnet werden muessen
	 *
	 * @return true wenn erfolgreich
	 * @return false im Fehlerfall
	 */
	public function abschluss($mitarbeiter_uid, $abrechnungsdatum, $verwendung_obj)
	{
		global $cfg_sv_altersabschlag, $cfg_sv_abschlaege, $cfg_lsttgl;
		$this->abschluss=true;
		$datum_obj = new datum();
		$dt_abrechnungsdatum = new DateTime($abrechnungsdatum);
		$this->log='';
		$this->dv_art = $verwendung_obj->dv_art;
		$this->mitarbeiter_uid=$mitarbeiter_uid;
		$this->abrechnungsdatum = $abrechnungsdatum;
		$this->tageabzurechnen=0;


		$mitarbeiter = new mitarbeiter();
		if(!$mitarbeiter->load($mitarbeiter_uid))
		{
			$this->errosmg = 'Fehler beim Laden des Mitabreiters';
			return false;
		}

		$vertrag = new vertrag();
		$vertrag->loadVertrag($mitarbeiter->person_id, false);
		$gesamtbetrag=0;
		$this->vertrag_arr=array();
		$this->log.="Verträge die abgerechnet werden:";

		foreach($vertrag->result as $row)
		{
			$this->log.="\n -> ".$row->bezeichnung.' - € '.$row->betrag.' (ID '.$row->vertrag_id.')';
			$gesamtbetrag+=$row->betrag;
			$this->vertrag_arr[]=$row->vertrag_id;
		}

		$this->honorar_gesamt = $gesamtbetrag;
		$this->log.="\n";

		// Anwesenheiten ermitteln
		$qry = "SELECT
					lehreinheit_id, tbl_lehrveranstaltung.bezeichnung
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE vertrag_id in(".$this->db_implode4SQL($this->vertrag_arr).")";

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
		if($anzahl_termine!=0)
			$anwesenheit_prozent = ($anzahl_anwesend/$anzahl_termine*100);
		else
			$anwesenheit_prozent = 0;
		$this->log.="\nAnwesenheit: $anzahl_anwesend/$anzahl_termine = ".number_format($anwesenheit_prozent,2)." % ";
		$this->log.="\n".$anwesenheitslog;

		$gesamttageabgerechnet=0;
		$abrechnungsdatumfound=0;
		$honorar_offen=0;
		$honorar_ausbezahlt=0;

		// Berechnung der Zurueckgehaltenen Sonderzahlungen
		$honorar_ausbezahlt = $this->getAbrechnungsbrutto($mitarbeiter_uid, $verwendung_obj->beginn, $verwendung_obj->ende);

		$honorar_offen = $this->honorar_gesamt - $honorar_ausbezahlt;

		$this->log.="\nHonorar gesamt: ".number_format($this->honorar_gesamt,2);
		$this->log.="\nHonorar ausbezahlt: ".number_format($honorar_ausbezahlt,2);
		$this->log.="\nHonorar offen: ".number_format($honorar_offen,2);
		$this->log.="\nAbzug für nicht anwesende Stunden: ".number_format($anwesenheitsabzug,2);
		$this->log.="\n---------------------------------";
		$honorar_offen = $honorar_offen - $anwesenheitsabzug;
		$this->log.="\nSaldo: ".number_format($honorar_offen,2);

		$this->brutto = $honorar_offen;
		$this->netto = $honorar_offen;

		return true;
	}

	/**
	 * Prueft ob eine Abrechnung die letzte vorhandene ist
	 *
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 * @param $abrechnungsdatum Datum der Abrechnung
	 * @return true wenn es die letzte abrechnung ist
	 * @reutrn false wenn nicht
	 */
	public function isletzteAbrechnung($mitarbeiter_uid, $abrechnungsdatum)
	{
		$qry = "SELECT
					1
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum>".$this->db_add_param($abrechnungsdatum);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return false;
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Loescht die Abrechnung eines Mitarbeiters
	 *
	 * @param $mitabreiter_uid
	 * @param $abrechnungsdatum
	 * @return boolean
	 */
	public function deleteAbrechnung($mitarbeiter_uid, $abrechnungsdatum)
	{
		$qry = "DELETE FROM addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum=".$this->db_add_param($abrechnungsdatum);

		//TODO Buchung mit Mitarbeiter entfernen

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Abrechnung';
			return false;
		}
	}


	/**
	 * Berechnet den Abzug des Gesamthonorars aufgrund der Stunden die der Lektor nicht anwesend war
	 * @param $username UID des Mitabreiters
	 * @param $vertrag_arr Array mit den VertragsIDs
	 * @param $abrechnungsdatum Datum der Abrechnung
	 * @return abzug vom Gesamthonorar
	 */
	public function loadAnwesenheitsabzug($username, $vertrag_arr, $abrechnungsdatum)
	{
		if(count($vertrag_arr)==0)
			return false;
		$qry = "SELECT lehreinheit_id, tbl_lehrveranstaltung.bezeichnung
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				WHERE
					tbl_lehreinheitmitarbeiter.vertrag_id in(".$this->db_implode4SQL($vertrag_arr).");";
		$gesamtabzug =0;
		$datum_obj = new datum();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$anwesenheit = new anwesenheit();
				$anwesenheit->loadAnwesenheitMitarbeiter($username, $row->lehreinheit_id);

				foreach($anwesenheit->anwesenheit as $anw)
				{
					if($anw['datum']<=$abrechnungsdatum && $anw['anwesend']==false)
					{
						$abzug = $anw['einheiten']*$anw['stundensatz'];
						$this->log.="\n".$row->bezeichnung.' am '.$datum_obj->formatDatum($anw['datum'],'d.m.Y').' - '.$anw['einheiten'].' Einheiten á '.$anw['stundensatz'].' = '.$abzug;
						$gesamtabzug+=$abzug;
					}
				}
			}
			return $gesamtabzug;
		}
		return false;
	}

	/**
	 * Prueft ob eine Abrechnung die letzte vorhandene ist
	 *
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 * @param $abrechnungsdatum Datum der Abrechnung
	 * @return true wenn es die letzte abrechnung ist
	 * @reutrn false wenn nicht
	 */
	public function loadAbschluss($mitarbeiter_uid, $abrechnungsdatum)
	{
		$qry = "SELECT
					*
				FROM
					addon.tbl_abrechnung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum=".$this->db_add_param($abrechnungsdatum)."
					AND abschluss=true
					AND kostenstelle_id is null";

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
				$this->brutto = $row->brutto;
				$this->netto = $row->netto;
				$this->lst_lfd = $row->lst_lfd;
				$this->log = $row->log;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
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
	 * Laedt die Abrechnung zu einem Abrechnungsdatum
	 * @param abrechnugsdatum
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAbrechnungenDatum($abrechnungsdatum)
	{
		$qry = "SELECT
					*
				FROM
					addon.tbl_abrechnung
				WHERE
					abrechnungsdatum=".$this->db_add_param($abrechnungsdatum)."
					AND kostenstelle_id is null
					AND abschluss=false
				";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new abrechnung();

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
				$obj->brutto = $row->brutto;
				$obj->netto = $row->netto;
				$obj->lst_lfd = $row->lst_lfd;
				$obj->log = $row->log;

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
	 * Laedt die Aufteilung eines Abrechnungsdatums
	 *
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 * @param $abrechnungsdatum Datum der Abrechnung
	 * @return true wenn es die letzte abrechnung ist
	 * @reutrn false wenn nicht
	 */
	public function loadAufteilung($mitarbeiter_uid, $abrechnungsdatum)
	{
		$qry = "SELECT
					tbl_abrechnung.*, tbl_kostenstelle.kostenstelle_nr
				FROM
					addon.tbl_abrechnung
					JOIN wawi.tbl_kostenstelle USING (kostenstelle_id)
				WHERE
					mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND abrechnungsdatum=".$this->db_add_param($abrechnungsdatum)."
					AND abschluss=false
					AND kostenstelle_id is NOT NULL";

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
				$obj->brutto = $row->brutto;
				$obj->netto = $row->netto;
				$obj->lst_lfd = $row->lst_lfd;
				$obj->log = $row->log;
				$obj->kostenstelle_nr = $row->kostenstelle_nr;

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
	 * Liefert alle Personen die zu einem Abrechnungsdatum abgerechnet werden muessen
	 */
	public function getPersonenAbrechnung($abrechnungsdatum)
	{
		$qry = "SELECT
					distinct tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre, tbl_person.titelpost,
					tbl_mitarbeiter.mitarbeiter_uid
				FROM
					bis.tbl_bisverwendung
					JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
					JOIN public.tbl_benutzer ON(tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid)
					JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
				WHERE
					".$this->db_add_param($abrechnungsdatum)." between tbl_bisverwendung.beginn AND tbl_bisverwendung.ende
					AND NOT tbl_mitarbeiter.fixangestellt";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpre = $row->titelpre;
				$obj->titlepost  = $row->titelpost;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;

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
	 * Prueft ob dieser Vertrag bereits bei einer Abrechnung berücksichtigt/abgerechnet wurde
	 * @param $vertrag_id
	 * @return boolean true wenn abgerechnet false wenn nicht
	 */
	public function isTeilabgerechnet($vertrag_id)
	{
		/*
		 - Hat es schon den status abgerechnet dann darf gar nicht gelöscht werden
		 - Wenn es ein Lehrauftrag ist, dann das Datum der 1. Stunde ermitteln und schauen ob es eine Abrechnung nach dem datum gibt.
		 - Wenn es ein Sonderhonorar ist dann das Datum ermitteln und schauen ob es eine Abrechnung nach dem Datum gibt.
		*/
		$vertrag = new vertrag();
		if($vertrag->getStatus($vertrag_id, 'abgerechnet'))
			return true;

		$qry = "SELECT 1 FROM addon.tbl_abrechnung
				WHERE
					abrechnungsdatum>=(
						SELECT min(tbl_stundenplan.datum) FROM
						lehre.tbl_vertrag
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(vertrag_id)
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN lehre.tbl_stundenplan USING(lehreinheit_id)
						WHERE
							vertrag_id=".$this->db_add_param($vertrag_id)."
						GROUP BY tbl_stundenplan.datum LIMIT 1)
					AND mitarbeiter_uid=(SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter
				 		WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).")
				UNION
				SELECT 1 FROM addon.tbl_abrechnung
				WHERE
					abrechnungsdatum>=(SELECT vertragsdatum FROM lehre.tbl_vertrag
						WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).")
					AND mitarbeiter_uid in(SELECT uid FROM public.tbl_benutzer
 						WHERE person_id=(SELECT person_id FROM lehre.tbl_vertrag where vertrag_id=".$this->db_add_param($vertrag_id)."))
					AND NOT EXISTS(SELECT 1 FROM lehre.tbl_lehreinheitmitarbeiter WHERE vertrag_id=".$this->db_add_param($vertrag_id).")
				";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
