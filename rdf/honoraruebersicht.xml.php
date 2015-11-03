<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/bankverbindung.class.php');
require_once('../../../include/wawi_konto.class.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/anwesenheit.class.php');
require_once('../../../include/vertrag.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/stunde.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../include/abrechnung.class.php');

if(isset($_SERVER['REMOTE_USER']))
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('addon/abrechnung'))
		die('Sie haben keine Berechtigung für diese Seite');
}

if(isset($_GET['abrechnungsmonat']))
{
	$abrechnungsmonat = $_GET['abrechnungsmonat'];
	$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
	$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
	$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));
}
else
	die('Parameter abrechnungsmonat fehlt');

$abrechnung = new abrechnung();
$abrechnung->getPersonenAbrechnung($abrechnungsdatum);

$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getSemesterFromDatum($abrechnungsdatum);
$stsem_obj->load($stsem);

$studiengang = new studiengang();
$studiengang->getAll(null, false);

echo '<honorare>';
$datum_obj = new datum();
$db = new basis_db();
$stunde = new stunde();
$stunde->loadAll();
$stunden_arr = array();
foreach($stunde->stunden as $row)
{
	$stunden_arr[$row->stunde]['von']=$row->beginn;
	$stunden_arr[$row->stunde]['bis']=$row->ende;
}
foreach($abrechnung->result as $row)
{
	echo '<honorar>';

	$mitarbeiter = new mitarbeiter();
	$mitarbeiter->load($row->mitarbeiter_uid);

	echo '<anrede><![CDATA['.$mitarbeiter->anrede.']]></anrede>
		<titelpre><![CDATA['.$mitarbeiter->titelpre.']]></titelpre>
		<vorname><![CDATA['.$mitarbeiter->vorname.']]></vorname>
		<nachname><![CDATA['.$mitarbeiter->nachname.']]></nachname>
		<titelpost><![CDATA['.$mitarbeiter->titelpost.']]></titelpost>
		<personalnummer><![CDATA['.$mitarbeiter->personalnummer.']]></personalnummer>
		<datum_aktuell><![CDATA['.date('d.m.Y').']]></datum_aktuell>
		<lektor_kurzbz><![CDATA['.$mitarbeiter->kurzbz.']]></lektor_kurzbz>
		<svnr><![CDATA['.$mitarbeiter->svnr.']]></svnr>
		';

		$adresse = new adresse();
		$adresse->loadZustellAdresse($mitarbeiter->person_id);

	echo '
		<strasse><![CDATA['.$adresse->strasse.']]></strasse>
		<plz><![CDATA['.$adresse->plz.']]></plz>
		<ort><![CDATA['.$adresse->ort.']]></ort>';

	$bisverwendung = new bisverwendung();
	$bisverwendung->getLastVerwendung($mitarbeiter->uid);

	echo '
		<dv_art><![CDATA['.$bisverwendung->dv_art.']]></dv_art>
		<anmeldedatum><![CDATA['.$datum_obj->formatDatum($bisverwendung->beginn,'d.m.Y').']]></anmeldedatum>
		<abmeldedatum><![CDATA['.$datum_obj->formatDatum($bisverwendung->ende,'d.m.Y').']]></abmeldedatum>';

	$bankverbindung = new bankverbindung();
	$bankverbindung->load_pers_verrechnung($mitarbeiter->person_id);

	echo '
		<bank_bezeichnung><![CDATA['.$bankverbindung->name.']]></bank_bezeichnung>
		<bank_iban><![CDATA['.$bankverbindung->iban.']]></bank_iban>
		<bank_bic><![CDATA['.$bankverbindung->bic.']]></bank_bic>
		<bank_blz><![CDATA['.$bankverbindung->blz.']]></bank_blz>
		<bank_kontonummer><![CDATA['.$bankverbindung->kontonr.']]></bank_kontonummer>';

	$wawi_konto = new wawi_konto();
	$wawi_konto->getKontoPerson($mitarbeiter->person_id);

	$wawi_kontonr='';
	if(isset($wawi_konto->result[0]))
		$wawi_kontonr = $wawi_konto->result[0]->kontonr;

	echo '<wawi_konto><![CDATA['.$wawi_kontonr.']]></wawi_konto>';

	$vertrag = new vertrag();
	$vertrag->getVertragFromDatum($mitarbeiter->uid, $abrechnungsdatum);

	$sonderhonorar=array();
	$lehrauftrag=array();
	$selbststudium=array();
	foreach($vertrag->result as $row_vertrag)
	{
		$vertragdetail = new vertrag();
		$vertragdetail->loadZugeordnet($row_vertrag->vertrag_id);

		if(count($vertragdetail->result)>0)
		{
			foreach($vertragdetail->result as $row_detail)
			{
					$lehreinheit_id = $row_detail->lehreinheit_id;

					if(!isset($lem_arr[$lehreinheit_id][$mitarbeiter->uid]))
					{
						$lem = new lehreinheitmitarbeiter();
						$lem->load($row_detail->lehreinheit_id,$mitarbeiter->uid);
						$lem_arr[$lehreinheit_id][$mitarbeiter->uid]=$lem;
					}

					if(!isset($le_arr[$lehreinheit_id]))
					{
						$lehreinheit = new lehreinheit();
						$lehreinheit->load($lehreinheit_id);
						$le_arr[$lehreinheit_id]=$lehreinheit;
					}

					$lehrveranstaltung_id = $le_arr[$lehreinheit_id]->lehrveranstaltung_id;

					if(!isset($lv_arr[$lehrveranstaltung_id]))
					{
						$lehrveranstaltung = new lehrveranstaltung();
						$lehrveranstaltung->load($lehrveranstaltung_id);
						$lv_arr[$lehrveranstaltung_id]=$lehrveranstaltung;
					}
					$stg = $studiengang->kuerzel_arr[$lv_arr[$lehrveranstaltung_id]->studiengang_kz];
					$lehrauftrag[] = array('bezeichnung'=>$row_detail->bezeichnung,
						'lehreinheit_id'=>$lehreinheit_id,
						'bezeichnung'=>$stg.$lv_arr[$lehrveranstaltung_id]->semester.'-'.$lv_arr[$lehrveranstaltung_id]->kurzbz.'-'.$le_arr[$lehreinheit_id]->lehrform_kurzbz,
						'stundensatz'=>$lem_arr[$lehreinheit_id][$mitarbeiter->uid]->stundensatz,
						'semesterstunden'=>$lem_arr[$lehreinheit_id][$mitarbeiter->uid]->semesterstunden,
						'gesamt'=>$row_vertrag->betrag);

					if($lem_arr[$lehreinheit_id][$mitarbeiter->uid]->semesterstunden != $lem_arr[$lehreinheit_id][$mitarbeiter->uid]->planstunden)
					{
						$selbststudium[] = array('bezeichnung'=>$row_detail->bezeichnung,
								'lehreinheit_id'=>$lehreinheit_id,
								'bezeichnung'=>$stg.$lv_arr[$lehrveranstaltung_id]->semester.'-'.$lv_arr[$lehrveranstaltung_id]->kurzbz.'-'.$le_arr[$lehreinheit_id]->lehrform_kurzbz,
								'stundensatz'=>$lem_arr[$lehreinheit_id][$mitarbeiter->uid]->stundensatz,
								'semesterstunden'=>($lem_arr[$lehreinheit_id][$mitarbeiter->uid]->semesterstunden - $lem_arr[$lehreinheit_id][$mitarbeiter->uid]->planstunden),
								'gesamt'=>($lem_arr[$lehreinheit_id][$mitarbeiter->uid]->semesterstunden - $lem_arr[$lehreinheit_id][$mitarbeiter->uid]->planstunden)*$lem_arr[$lehreinheit_id][$mitarbeiter->uid]->stundensatz);
					 }


					$qry_stunde = "SELECT min(stunde) as von, max(stunde) as bis, datum
						FROM
							lehre.tbl_stundenplan
						WHERE
							lehreinheit_id=".$db->db_add_param($lehreinheit_id)."
							AND mitarbeiter_uid=".$db->db_add_param($mitarbeiter->uid)."
							AND datum>=".$db->db_add_param($stsem_obj->start)."
							AND datum<=".$db->db_add_param($stsem_obj->ende)."
						GROUP BY datum ORDER BY datum";

					$von='';
					$bis='';
					$datum='';
					if($result_stunde = $db->db_query($qry_stunde))
					{
						while($row_stunde = $db->db_fetch_object($result_stunde))
						{
							$von = $stunden_arr[$row_stunde->von]['von'];
							$bis = $stunden_arr[$row_stunde->bis]['bis'];
							$datum = $row_stunde->datum;
							echo '<termine>
									<lehreinheit_id><![CDATA['.$row_detail->lehreinheit_id.']]></lehreinheit_id>
									<datum><![CDATA['.$datum_obj->formatDatum($datum,'d.m.Y').']]></datum>
									<von><![CDATA['.$von->format('H:i').']]></von>
									<bis><![CDATA['.$bis->format('H:i').']]></bis>
									<einheiten><![CDATA['.($row_stunde->bis-$row_stunde->von+1).']]></einheiten>
									<honorar><![CDATA['.$lem->stundensatz.']]></honorar>
									<vertragsnummer><![CDATA['.$row_vertrag->bezeichnung.']]></vertragsnummer>';
							echo '</termine>';
						}
					}
				}
			}
			else
			{
				// Sonderhonorar zB Pruefung
				$sonderhonorar[]=array('bezeichnung'=>$row_vertrag->bezeichnung,
					'gesamt'=>$row_vertrag->betrag,
					'datum'=>$row_vertrag->vertragsdatum);
			}
	}

	foreach($lehrauftrag as $row_lehrauftrag)
	{
		echo '<lehrauftrag>
				<lehreinheit_id><![CDATA['.$row_lehrauftrag['lehreinheit_id'].']]></lehreinheit_id>
				<bezeichnung><![CDATA['.$row_lehrauftrag['bezeichnung'].']]></bezeichnung>
				<stundensatz><![CDATA['.$row_lehrauftrag['stundensatz'].']]></stundensatz>
				<semesterstunden><![CDATA['.number_format($row_lehrauftrag['semesterstunden'],1).']]></semesterstunden>
				<gesamt><![CDATA['.$row_lehrauftrag['gesamt'].']]></gesamt>
			</lehrauftrag>';
	}

	foreach($selbststudium as $row_selbststudium)
	{
		echo '<selbststudium>
				<lehreinheit_id><![CDATA['.$row_selbststudium['lehreinheit_id'].']]></lehreinheit_id>
				<bezeichnung><![CDATA['.$row_selbststudium['bezeichnung'].']]></bezeichnung>
				<stundensatz><![CDATA['.$row_selbststudium['stundensatz'].']]></stundensatz>
				<semesterstunden><![CDATA['.number_format($row_selbststudium['semesterstunden'],1).']]></semesterstunden>
				<gesamt><![CDATA['.$row_selbststudium['gesamt'].']]></gesamt>
			</selbststudium>';
	}

	foreach($sonderhonorar as $row_sonderhonorar)
	{
		echo '<sonderhonorar>
				<datum><![CDATA['.$datum_obj->formatDatum($row_sonderhonorar['datum'],'d.m.Y').']]></datum>
				<bezeichnung><![CDATA['.$row_sonderhonorar['bezeichnung'].']]></bezeichnung>
				<gesamt><![CDATA['.$row_sonderhonorar['gesamt'].']]></gesamt>
			</sonderhonorar>';
	}
	echo '</honorar>';
}
echo '</honorare>';
