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
 * Korrigiert die bestehenden Verwendungen und setzt das Startdatum
 * neu wenn sich die erste Stunde eines Lektors geaendert hat.
 * Das Beginndatum wird nur korrigiert wenn der Lektor noch nicht abgerechnet wurde.
 *
 * Aufruf über Commandline mit Mailversand: php korrigiere_verwendung.php --mailto info@fhcomplete.org
 */
require_once(dirname(__FILE__).'/../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../../include/mitarbeiter.class.php');
require_once(dirname(__FILE__).'/../../../include/bisverwendung.class.php');
require_once(dirname(__FILE__).'/../../../include/mail.class.php');
require_once(dirname(__FILE__).'/../include/abrechnung.class.php');
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
$db = new basis_db();

$stsem = new studiensemester();
$studiensemester_kurzbz = $stsem->getAktOrNext();
if(!$stsem->load($studiensemester_kurzbz))
{
	die('Fehler beim Laden des Studiensemesters');
}

// Commandline Paramter parsen bei Aufruf ueber Cronjob
// zb php korrigiere_verwendung.php --mailto info@fhcomplete.org
$longopt = array(
  "mailto:"
);
$commandlineparams = getopt('', $longopt);
if(isset($commandlineparams['mailto']))
	$mailto=$commandlineparams['mailto'];
elseif(isset($_GET['mailto']))
	$mailto=$_GET['mailto'];
else
	$mailto='';

$mailmessage='';

// Alle Personen holen bei denen das Startdatum nicht korrekt ist
$qry = "SELECT
			tbl_mitarbeiter.mitarbeiter_uid, min(datum) as beginn
		FROM
			lehre.tbl_stundenplan
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN public.tbl_mitarbeiter ON(tbl_stundenplan.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid)
		WHERE
			tbl_mitarbeiter.fixangestellt=false
			AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
		GROUP by tbl_mitarbeiter.mitarbeiter_uid";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$bisvw = new bisverwendung();
		if($bisvw->getLastVerwendung($row->mitarbeiter_uid))
		{
			if($bisvw->beginn!=$row->beginn)
			{
				// Datum der ersten Stunde hat sich geaendert

				$error = false;

				// Pruefen ob bereits abgerechnet wurde
				$abrechnung = new abrechnung();
				if($abrechnung->getLetzteAbrechnung($row->mitarbeiter_uid))
				{
					if($abrechnung->abrechnungsdatum > $bisvw->beginn)
					{
						// Es gab bereits eine Abrechnung mit dem alten Beginndatum
						outmessage($row->mitarbeiter_uid, 'wurde bereits abgerechnet -> Datum wird nicht korrigiert (ist: '.$bisvw->beginn.' soll:'.$row->beginn.')');
						$error = true;
					}
					elseif($abrechnung->abrechnungsdatum > $row->beginn)
					{
						// Es gab bereits eine Abrechnung vor dem neuen Beginndatum
						outmessage($row->mitarbeiter_uid, 'Neues Datum liegt vor der letzten Abrechnung -> keine Korrektur');
						$error = true;
					}
				}

				// Passt die Verwendung ueberhaupt zum Semester
				if(!($bisvw->beginn <= $stsem->ende && $bisvw->ende >= $stsem->start))
				{
					$error = true;
					//outmessage($row->mitarbeiter_uid, 'Nicht passende Verwendung -> Uebersprungen');
				}

				if(!$error)
				{
					$datum_alt = $bisvw->beginn;
					$bisvw->beginn = $row->beginn;
					if($bisvw->save())
					{
						outmessage($row->mitarbeiter_uid, "Datum von $datum_alt auf $row->beginn korrigiert");
					}
					else
						outmessage($row->mitarbeiter_uid, $bisvw->errormsg);
				}
			}
		}
		else
		{
			outmessage($row->mitarbeiter_uid, 'Keine Verwendung gefunden');
		}
	}
}
echo 'Alle Zuteilungen korrigiert'.PHP_EOL;

if($mailto!='' && $mailmessage!='')
{
	$mailmessage = "Dies ist ein automatisches Mail.\nFolgende Korrekturen wurden an den BIS-Verwendungen vorgenommen:\n\n".$mailmessage;

	$mail = new mail($mailto, 'no-reply@'.DOMAIN,'Korrektur BIS-Verwendung',$mailmessage);

	if(!$mail->send())
		die('Fehler beim Senden des Mails!');
	else
		echo 'Mail verschickt an: '.$mailto;
}

function outmessage($mitarbeiter_uid, $message)
{
	global $mailmessage;
	$mitarbeiter = new mitarbeiter($mitarbeiter_uid);

	echo $mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$message.PHP_EOL.'<br>';
	$mailmessage .= $mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$message.PHP_EOL;
}
?>
