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
 * CSV Export der Abrechnungsdaten
 *
 * Format:
 * Abrechnungsmonat;Kundenummer;Personalnummer;;Lohnart;;;lfd_brutto;kostenstelle;
 */
require_once('../config.inc.php');
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/studiensemester.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/abrechnung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$db = new basis_db();
$stsem_obj = new studiensemester();

$abrechnungsmonat = (isset($_REQUEST['abrechnungsmonat'])?$_REQUEST['abrechnungsmonat']:((date('m')-1).'/'.date('Y')));

$sonderzahlung = (isset($_REQUEST['sz'])?true:false);

$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));
$stsem = $stsem_obj->getSemesterFromDatum($abrechnungsdatum);
$qry = "SELECT
		tbl_mitarbeiter.personalnummer, tbl_abrechnung.mitarbeiter_uid, tbl_abrechnung.brutto, tbl_kostenstelle.kostenstelle_nr
	FROM
		addon.tbl_abrechnung
		JOIN wawi.tbl_kostenstelle USING(kostenstelle_id)
		JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
	WHERE
		abrechnungsdatum=".$db->db_add_param($abrechnungsdatum)." AND kostenstelle_id is not null";
if($sonderzahlung)
	$qry.=" AND abschluss=true";
else
	$qry.=" AND abschluss=false";
$qry.=" ORDER BY personalnummer";
if($result = $db->db_query($qry))
{
	header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename=abrechnung'.$jahr.'_'.$monat.'.csv');

	$fp = fopen('php://output', 'w');

	while($row = $db->db_fetch_object($result))
	{
		// Wenn die Person nur in Lehrgaengen unterrichtet, dann ist die Lohnart 150 sonst 151
		$qry = "SELECT
				1
			FROM
				lehre.tbl_lehreinheitmitarbeiter
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)."
				AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)."
				AND studiengang_kz>0 AND studiengang_kz<10000";

		$lohnart=150;
		if($result_lohnart = $db->db_query($qry))
			if($db->db_num_rows($result_lohnart)>0)
				$lohnart=151;

		if($sonderzahlung)
			$lohnart=514;

		// Wenn Kostenstelle 800 dann ist die Lohnart 151
		if($row->kostenstelle_nr=='800')
			$lohnart = 151;

		//Abrechnungsmonat;Kundenummer;Personalnummer;;Lohnart;;;lfd_brutto;kostenstelle;
		$fields = array($monat, KUNDENNUMMER, $row->personalnummer,'', $lohnart,'','', number_format($row->brutto,2,',',''), $row->kostenstelle_nr);

		fputcsv($fp, $fields,';');

	}
	fclose($fp);
}
?>
