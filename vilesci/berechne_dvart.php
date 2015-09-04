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
/**
 * Berechnet die DV-Art
 */
require_once(dirname(__FILE__).'/../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(dirname(__FILE__).'/../config.inc.php');

// Wenn das Script nicht ueber Commandline gestartet wird, muss eine
// Authentifizierung stattfinden
if(php_sapi_name() != 'cli')
{
	$user = get_uid();

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('vertrag/mitarbeiter'))
		die('Sie haben keine Berechtigung fuer diese Seite');
}
$studiensemester_kurzbz=(isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'');

$db = new basis_db();

$datum_obj = new datum();

if($studiensemester_kurzbz=='')
{
	$stsem = new studiensemester();
	$studiensemester_kurzbz = $stsem->getAktOrNext();
}

if($studiensemester_kurzbz!='')
{

	$stsem = new studiensemester($studiensemester_kurzbz);

	$start = $stsem->start;
	$ende = $stsem->ende;
	// Daten holen
	$qry = "SELECT *,
				(SELECT sum(betrag)
				FROM lehre.tbl_vertrag
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(vertrag_id)
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
				WHERE
					tbl_lehrveranstaltung.studiengang_kz<0
					AND tbl_vertrag.person_id=a.person_id
				) as honorar_lehrgaenge
			 FROM (
			SELECT
				vorname, nachname, tbl_bisverwendung.beginn, tbl_bisverwendung.ende, person_id,tbl_bisverwendung.bisverwendung_id,tbl_bisverwendung.dv_art,
				sum(betrag) as gesamthonorar
			FROM
				lehre.tbl_vertrag
				JOIN campus.vw_mitarbeiter USING(person_id)
				JOIN bis.tbl_bisverwendung ON(uid=mitarbeiter_uid)
			WHERE
				NOT EXISTS(SELECT * FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz in ('storno','abgerechnet'))
				AND (tbl_bisverwendung.beginn is null
					OR (tbl_bisverwendung.beginn>=".$db->db_add_param($start)." AND tbl_bisverwendung.beginn<=".$db->db_add_param($ende)."))
				AND vw_mitarbeiter.fixangestellt=false
			GROUP BY vorname, nachname, tbl_bisverwendung.beginn, tbl_bisverwendung.ende, person_id, tbl_bisverwendung.bisverwendung_id, tbl_bisverwendung.dv_art) a
		   ";
//				AND tbl_vertrag.vertragsdatum>=".$db->db_add_param($start)." AND tbl_vertrag.vertragsdatum<=".$db->db_add_param($ende)."

//echo $qry.'<br><br>';
	if($result = $db->db_query($qry))
	{
		echo '<table>';
		echo '<tr><th>Vorname</th><th>Nachname</th><th>DV-Art Alt</th><th>DV-Art Neu</th><th>LG</th><th>Gesamt</th></tr>';
		while($row = $db->db_fetch_object($result))
		{
			$gesamthonorar = number_format($row->gesamthonorar,2,'.','');

			// Fiktivmonatsbezug berechnen
			// (Honorar gesamt/ Tage offen) * 30 / 7 * 6
			$tageoffen = BerechneGesamtTage($row->beginn, $row->ende);
			if($tageoffen!=0)
			{
				$fiktivmonatsbezug = ($row->gesamthonorar / $tageoffen) * 30 / 7 * 6;
				$fiktivmonatsbezug = number_format($fiktivmonatsbezug, 2,'.','');
			}
			else
				$fiktivmonatsbezug = '';

/*
			if((([geshon]/([semende]-[anmeldung])/7)*6)*30>405,98)
			{
				if([geshon]-[kostenstelle_850]-[kostenstelle_859])=0
					200
				else
					19
			}
			else
			{
				if(([geshon]-[kostenstelle_850]-[kostenstelle_859])=0)
					403
				else
					401
			}*/

			$kostenstelle859=0;
			$kostenstelle850=$row->honorar_lehrgaenge;
			if($fiktivmonatsbezug>SV_GERINGWERTIG)
			{
				//echo 'x:'.($gesamthonorar - $kostenstelle850 - $kostenstelle859);
				if(($gesamthonorar - $kostenstelle850 - $kostenstelle859)==0)
					$dv_art=200;
				else
					$dv_art=19;
			}
			else
			{
				//echo 'y:'.($gesamthonorar - $kostenstelle850 - $kostenstelle859);
				if(($gesamthonorar - $kostenstelle850 - $kostenstelle859)==0)
					$dv_art=403;
				else
					$dv_art=401;
			}
			$qry_upd = "UPDATE bis.tbl_bisverwendung SET dv_art=".$db->db_add_param($dv_art)." WHERE bisverwendung_id=".$db->db_add_param($row->bisverwendung_id);
			$db->db_query($qry_upd);
			echo '<tr><td>'.$row->vorname.'</td><td>'.$row->nachname.'</td><td>'.$row->dv_art.'</td><td>'.$dv_art.'</td><td>'.$row->honorar_lehrgaenge.'</td><td>'.$row->gesamthonorar.'</td></tr>';

		}
		echo '</table>';
	}
}
else
{
	echo '<!DOCTYPE HTML>
	<html>
	<head>
	<title>Vertraege</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body>
	<h2>Vertraege</h2>
	Studiensemester muss uebergeben werden
	</body>
	</html>
	';
}

function BerechneGesamtTage($startdatum, $endedatum)
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
?>
