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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * CSV Export LV61
 *
 * Format:
 * KLNR;pnr;famnr;NName;VName;titex;beruf;svnr;stra;plz;ort;gesch;ein;aus;kost;kzlg;dv;BIC;IBAN;bank;gkk;kobef;ubahn;dbdz;fibuk;ff8;
 */
require_once('../config.inc.php');
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/bankverbindung.class.php');
require_once('../../../include/adresse.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/abrechnung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$db = new basis_db();
$stsem_obj = new studiensemester();

$abrechnungsmonat = (isset($_REQUEST['abrechnungsmonat'])?$_REQUEST['abrechnungsmonat']:((date('m')-1).'/'.date('Y')));
$datum_obj = new datum();

$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
$abrechnungsdatum_start=date('Y-m-d',mktime(0,0,0,$monat,1, $jahr));
$abrechnungsdatum_ende=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));
$stsem = $stsem_obj->getSemesterFromDatum($abrechnungsdatum_start);
$qry = "SELECT
		tbl_mitarbeiter.personalnummer, tbl_person.vorname, tbl_person.nachname,
		tbl_person.titelpre, tbl_bisverwendung.dv_art, tbl_bisverwendung.beginn,
		tbl_bisverwendung.ende, tbl_person.svnr, tbl_person.geschlecht, tbl_person.person_id,
		(SELECT kontakt FROM public.tbl_kontakt WHERE person_id=tbl_person.person_id AND kontakttyp='email' ORDER BY zustellung DESC limit 1) as email
	FROM
		public.tbl_mitarbeiter
		JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
		JOIN public.tbl_person USING(person_id)
		JOIN bis.tbl_bisverwendung USING(mitarbeiter_uid)
	WHERE
		tbl_bisverwendung.beginn<=".$db->db_add_param($abrechnungsdatum_ende)."
		AND tbl_bisverwendung.ende>=".$db->db_add_param($abrechnungsdatum_start);

if($result = $db->db_query($qry))
{
	header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename=lv61_'.$jahr.'_'.$monat.'.csv');

	$fp = fopen('php://output', 'w');

	fputcsv($fp, array(
		'KLNR', // Kundennummer
		'pnr', // Personalnummer
		'famnr', // ?? immer 0
		'NName', // Nachname
		'VName', // Vorname
		'titex', // Titel
		'beruf', // immer Lektor
		'svnr', // Sozialversicherungsnummer
		'stra', // Strasse
		'plz', // Postleitzahl
		'ort', // Ort
		'gesch', // Geschlecht 1 = männlich 2=weiblich
		'ein', // Eintrittsdatum
		'aus', // Austrittsdatum ?? leer
		'kost', // ?? immer leer
		'kzlg', // ?? immer G
		'dv', // dv_art 19 / 401
		'BIC', // BIC
		'IBAN', // IBAN
		//'bank', // Name der Bank
		'gkk',  // ?? immer 0
		'kobef',// ?? immer 1
		'ubahn', // ?? immer 0
		'dbdz', // ?? immer 0
		'fibuk', // ?? immer 2
		'ff8', // ?? immer 2
		'email',
		'pdfpw'
	),';');
	while($row = $db->db_fetch_object($result))
	{
		$adresse = new adresse();
		$adresse->loadZustellAdresse($row->person_id);

		$bankverbindung = new bankverbindung();
		$bankverbindung->load_pers($row->person_id);
		if(isset($bankverbindung->result[0]))
			$bankverbindung = $bankverbindung->result[0];

		fputcsv($fp, array(
				KUNDENNUMMER, $row->personalnummer,'0', $row->nachname, $row->vorname,
				$row->titelpre,	'Lektor', $row->svnr, $adresse->strasse, $adresse->plz, $adresse->ort,
				($row->geschlecht=='m'?1:2), $datum_obj->formatDatum($row->beginn,'Ymd'),
				'', '', 'G', $row->dv_art,
				$bankverbindung->bic, $bankverbindung->iban,
				'0','1','0','0','2','2',
				$row->email, $row->svnr
			),';');
	}
	fclose($fp);
}
?>
