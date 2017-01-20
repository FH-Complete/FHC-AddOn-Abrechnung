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
 * FH-Complete Addon Abrechnung
 *
 * Berechnet den monatlichen Lohn fuer externe Mitarbeiter
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/vertrag.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/wawi_kostenstelle.class.php');
require_once('../../../include/datum.class.php');
require_once('../include/abrechnung.class.php');
require_once('functions.inc.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/projektbetreuer.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/abrechnung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$datum_obj = new datum();
$db = new basis_db();

$username = (isset($_REQUEST['username'])?$_REQUEST['username']:'');
$work = (isset($_REQUEST['work'])?$_REQUEST['work']:'');

if(isset($_REQUEST['abrechnungsmonat']))
	$abrechnungsmonat = $_REQUEST['abrechnungsmonat'];
else
{
	$dtnow = new DateTime();
	$dtago = $dtnow->sub(new DateInterval('P1M'));
	$abrechnungsmonat = $dtago->format('Y-m-t');;
}

$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:null);

echo '<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="Andreas Österreicher" >
	<link rel="icon" href="../skin/favicon.ico">
	<link href="../skin/bootstrap.min.css" rel="stylesheet">
	<link href="../skin/dashboard.css" rel="stylesheet">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css">
	<script type="text/javascript" src="../../../include/js/jquery1.9.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css"/>
	<link href="../skin/abrechnung.css" rel="stylesheet">
	<script src="../include/js/bootstrap.min.js"></script>
	<title>Abrechnung</title>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Abrechnung</a>
		</div>
        <div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="abrechnung.php">Mitarbeiter auswählen</a></li>
				<li><a href="abrechnung.php?work=nochnichtabgerechnet">Übersichtsliste</a></li>
				<li class="dropdown">
					<a href="#export" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Stammdaten <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu" data-name="data">
						<li><a href="abrechnung.php?work=generateVertraege">Verträge generieren</a></li>
						<li><a href="abrechnung.php?work=generateVerwendungAll">Verwendungen generieren</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#import" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Import <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu" data-name="data">
						<li><a href="abrechnung.php?work=csvimport">Import</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#export" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Export <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu" data-name="data">
						<li><a href="abrechnung.php?work=csvexport">LV60-Export</a></li>
						<li><a href="abrechnung.php?work=csvexport&sz">LV60sz-Export</a></li>
						<li><a href="abrechnung.php?work=csvexport&lv61">LV61-Export</a></li>
						<li><a href="../../../content/statistik/vertragsuebersicht.xls.php">SV-Export</a></li>
						<li><a href="abrechnung.php?work=honuebersicht">Honoraruebersicht</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-3 col-md-2 sidebar">
			<ul class="nav nav-sidebar">';

if($username!='')
{
	$mitarbeiter = new mitarbeiter();
	if(!$mitarbeiter->load($username))
	{
		echo '<span class="error">'.$mitarbeiter->errormsg.'</span>';
		$username='';
	}

	echo '<br><strong>'.$db->convert_html_chars($mitarbeiter->nachname.' '.$mitarbeiter->vorname).'</strong>';
	echo '<li '.($work!='uebersicht'?'class="active"':'').'><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'">Abrechnung</a></li>';
	echo '<li '.($work=='uebersicht'?'class="active"':'').'><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'&work=uebersicht">Übersicht</a></li>';

}
echo '
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
';

if($username=='')
{
	if($work=='nochnichtabgerechnet')
	{
		echo '<h1 class="page-header">Übersicht</h1>';

		showNochNichtAbgerechnet($abrechnungsmonat);
	}
	elseif($work=='alleabrechnen')
	{
		alleAbrechnen($abrechnungsmonat);
	}
	elseif($work=='generateVertraege')
	{
		echo '<h1 class="page-header">Verträge generieren</h1>';
		if(isset($_POST['do']) && $_POST['do']=='generate')
			generateVertraege($studiensemester_kurzbz);
		showFehlendeVertraege($studiensemester_kurzbz);
	}
	elseif($work=='generateVerwendungAll')
	{
		echo '<h1 class="page-header">Verwendung generieren</h1>';
		if(isset($_POST['do']) && $_POST['do']=='generate')
			generateVerwendung($studiensemester_kurzbz);
		showFehlendeVerwendung($studiensemester_kurzbz);
	}
	elseif($work=='honuebersicht')
	{
		echo '<h1 class="page-header">Honoraruebersicht</h1>

		Bitte wählen Sie das zu exportierende Monat:<br><br>
		<form action="../../../content/pdfExport.php" method="GET">
		<input type="hidden" name="xsl" value="HonUebersicht" />
		<input type="hidden" name="xml" value="honoraruebersicht.xml.php" />
		<input type="hidden" name="output" value="pdf" />
		';
		printAbrechnungsmonatDropDown();
		echo '<input type="submit" value="Erstellen" />';
		echo '</form>';

	}
	elseif($work=='csvimport')
	{
		include("csvimport.inc.php");
	}
	elseif($work=='csvexport')
	{
		if(isset($_GET['lv61']))
		{
			echo '<h1 class="page-header">CSV Export LV61</h1>

				Bitte wählen Sie das zu exportierende Monat:<br><br>
			<form action="lv61.php">';

			printAbrechnungsmonatDropDown();
			echo '<input type="submit" value="Exportieren" />';
			echo '</form>';
		}
		else
		{
			if(isset($_GET['sz']))
				$sonderzahlung=true;
			else
				$sonderzahlung=false;

			echo '<h1 class="page-header">CSV Export '.($sonderzahlung?'Sonderzahlung':'').'</h1>

				Bitte wählen Sie das zu exportierende Monat:<br><br>
			<form action="csvexport.php">';

			if($sonderzahlung)
				echo '<input type="hidden" name="sz" value="1" />';

			printAbrechnungsmonatDropDown();
			echo '<input type="submit" value="Exportieren" />';
			echo '</form>';
		}
	}
	else
	{
		echo '<h1 class="page-header">Abrechnung</h1>';

		// Autocomplete Feld fuer Mitarbeiter Auswahl
		echo '
		<form action="abrechnung.php" name="sendform" method="POST">

		Tippen sie den Namen ein um einen Mitarbeiter zu suchen:<br>
		Mitarbeiter: <input id="username" name="username" type="text" maxlength="32" value="'.$db->convert_html_chars($username).'">
		<script type="text/javascript">
			$(document).ready(function()
			{
				$("#username").autocomplete({
					source: "abrechnung_autocomplete.php?work=mitarbeiter",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value=ui.content[i].uid;
							ui.content[i].label=ui.content[i].titelpre+" "+ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].titelpost+" ("+ui.content[i].uid+")";
						}
					},
					select: function(event, ui)
					{
						ui.item.value=ui.item.uid;
					}
				});
		  });
		</script>';

		printAbrechnungsmonatDropDown();

		echo '
		<br><br>

		<input type="submit" value="Abrechnung starten" />
		</form>
		';
	}
}

// *** Abrechnungsansicht ***
if($username!='')
{
	if($work=='uebersicht')
	{
		// Uebersichtsliste ueber alle Abrechungsmonate des Semesters anzeigen
		printAbrechnungsuebersicht($username, $studiensemester_kurzbz);
	}
	else
	{
		// Monatsabrechnung anzeigen
		echo '<h1 class="page-header">Abrechnung</h1>';
		$abrechnungsdatum = $abrechnungsmonat;

		$next = getNextAbrechnungsdatum($abrechnungsdatum);
		$prev = getPrevAbrechnungsdatum($abrechnungsdatum);

		echo '<div class="pull-left"><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$prev.'">&lt;&lt; vorheriges Monat</a></div>';
		echo '<div class="pull-right"><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$next.'">nächstes Monat &gt;&gt;</a></div><br>';

		$dtabrechnung = new DateTime($abrechnungsmonat);
		$bezeichnung = $monatsname[1][$dtabrechnung->format('n')-1].' '.$dtabrechnung->format('Y').' ('.$abrechnungsmonat.')';
		echo ' Abrechnungsmonat: '.$bezeichnung;

		if($work=='generateVerwendung')
		{
			if(($errormsg = generateVerwendungMitarbeiter($username))!==true)
				echo '<span class="error">'.$errormsg.'</span>';

		}
		elseif($work=='deleteAbrechnung')
		{
			$abrechnung = new abrechnung();
			if($abrechnung->deleteAbrechnung($username, $abrechnungsdatum))
				echo '<br><span class="ok">Abrechnung wurde erfolgreich entfernt</span>';
			else
				echo '<br><span class="error">Fehler beim Löschen der Abrechnung</span>';
		}

		// Alle noch nicht abgerechneten Verträge anzeigen
		printVertragsuebersicht($mitarbeiter->person_id, $abrechnungsdatum);

		// BIS-Verwendung laden
		if(($verwendung_obj = getVerwendung($username, $abrechnungsdatum))!==false)
		{
			$abrechnung=new abrechnung();

			// Nachschauen ob fuer dieses Monat schon eine Abrechnung vorhanden ist
			if(!$abrechnung->exists($username, $abrechnungsdatum))
			{
				// Abrechnung vorberechnen
				if(!$abrechnung->abrechnung($username, $abrechnungsdatum, $verwendung_obj))
				{
					echo '<span class="error">Failed:'.$abrechnung->errormsg.'</span>';
				}
				else
				{
					echo '
					<form action="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'" method="POST">
						<input type="hidden" name="work" value="abrechnen" />
						<input type="submit" value="Abrechnen" />
					</form>';

					if($work=='abrechnen')
					{
						// Abrechnung speichern
						$abrechnung->loadVertragsAufteilung();

						if($abrechnung->saveAbrechnung())
						{
							// Seite neu laden
							echo '<script>window.location.href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'"</script>';
						}
						else
						{
							// Fehler beim speichern
							echo '<span class="error">'.$abrechnung->errormsg.'</span>';
						}
					}
					else
					{
						echo '<h2>Vorschau:</h2>';
					}
					echo '<div style="background-color:white; overflow:auto; border: 1px solid black; padding:5px;">';
					echo nl2br($abrechnung->log);
					echo '</div>';
				}
			}
			else
			{
				echo 'Dieser Monat wurde bereits abgerechnet';
				if($abrechnung->isletzteAbrechnung($username, $abrechnungsdatum))
				{
					// Die jeweils letzt Abrechnung kann wieder geloescht werden
					echo '<script>
							function confirmDelete()
							{
								return confirm("Sind Sie sicher dass Sie diese Abrechnung löschen wollen?");
							}
						</script>
						<form style="display: inline" action="abrechnung.php?username='.$db->convert_html_chars($username).'&abrechnungsmonat='.$db->convert_html_chars($abrechnungsmonat).'" method="POST" onsubmit="return confirmDelete()">
						<input type="hidden" name="work" value="deleteAbrechnung" />
						<input type="submit" value="diese Abrechnung löschen" />
						</form>
						';
				}

				if($abrechnung->abschlussNoetig($username, $abrechnungsdatum, $verwendung_obj))
				{
					// Beim Abschluss wird
					// - Anwesenheiten geprueft
					// - Monatssechstel ausbezahlt
					// - Vertraege auf abgerechnet gesetzt

					// Vorschau fuer den Abschluss
					$abrechnung->abschluss($username, $abrechnungsdatum, $verwendung_obj);

					echo '
					<form action="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'" method="POST">
						<input type="hidden" name="work" value="abschliessen" />
						<input type="submit" value="Abschluss durchf&uuml;hren" />
					</form>';

					if($work=='abschliessen')
					{
						// Abschluss durchfuehren
						$abrechnung->loadVertragsAufteilung();
						if($abrechnung->saveAbrechnung())
						{

							// Vertraege auf Abgerechnet setzen
							foreach($abrechnung->vertrag_arr as $vertrag_id)
							{
								$vertrag = new vertrag();
								$vertrag->vertragsstatus_kurzbz='abgerechnet';
								$vertrag->vertrag_id = $vertrag_id;
								$vertrag->uid = $uid;
								$vertrag->datum = date('Y-m-d H:i:s');
								$vertrag->insertvon = $uid;
								$vertrag->saveVertragsstatus(true);
							}

							// Seite neu laden
							echo '<script>window.location.href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$abrechnungsmonat.'"</script>';
						}
						else
						{
							echo '<span class="error">'.$abrechnung->errormsg.'</span>';
						}
					}

					echo '<h2>Abschluss Vorschau</h2>';
					echo '<div style="background-color:white; overflow:auto; border: 1px solid black; padding:5px;">';
					echo nl2br($abrechnung->log);
					echo '</div>';

					// Monatsabrechnung anzeigen
					$abrechnung->getAbrechnungMitarbeiter($username, $abrechnungsdatum);
					echo '<h2>Abrechnungsdetails</h2>';
					echo '<div class="abrechnungsdetails">';
					echo nl2br($abrechnung->log);
					echo '</div>';
				}
				else
				{
					if($abrechnung->getAbrechnungMitarbeiter($username, $abrechnungsdatum))
					{
						echo '<h2>Abrechnungsdetails</h2>';
						echo '<div class="abrechnungsdetails">';
						echo nl2br($abrechnung->log);
						echo '<hr><br>SV Laufend:'.$abrechnung->sv_lfd;
						echo '<br>SV Satz:'.$abrechnung->sv_satz;
						echo '<br>SV Teiler:'.$abrechnung->sv_teiler;
						echo '<br>Honorar Durchgefuehrt:'.$abrechnung->honorar_dgf;
						echo '<br>Honorar Offen:'.$abrechnung->honorar_offen;
						echo '<br>Brutto:'.$abrechnung->brutto;
						echo '<br>Netto:'.$abrechnung->netto;
						echo '<br>Lst Lfd:'.$abrechnung->lst_lfd;
						echo '</div>';
					}
					else
						echo 'Load Failed:'.$abrechnung->errormsg;

					// Abschlussabrechnung anzeigen falls vorhanden
					if($abrechnung->loadAbschluss($username, $abrechnungsdatum))
					{
						echo '<h2>Abschlussabrechnung</h2>';
						echo '<div class="abrechnungsdetails">';
						echo nl2br($abrechnung->log);
						echo '<hr><br>SV Laufend:'.$abrechnung->sv_lfd;
						echo '<br>SV Satz:'.$abrechnung->sv_satz;
						echo '<br>SV Teiler:'.$abrechnung->sv_teiler;
						echo '<br>Honorar Durchgefuehrt:'.$abrechnung->honorar_dgf;
						echo '<br>Honorar Offen:'.$abrechnung->honorar_offen;
						echo '<br>Brutto:'.$abrechnung->brutto;
						echo '<br>Netto:'.$abrechnung->netto;
						echo '<br>Lst Lfd:'.$abrechnung->lst_lfd;
						echo '</div>';
					}
				}
			}
		}
		else
		{
			echo '<br>Es wurde keine gültige Verwendung gefunden für das Abrechnungsdatum '.$datum_obj->formatDatum($abrechnungsdatum,'d.m.Y').'
			<form action="abrechnung.php?username='.$db->convert_html_chars($username).'&abrechnungsmonat='.$db->convert_html_chars($abrechnungsmonat).'" method="POST">
				<input type="hidden" name="work" value="generateVerwendung" />
				<input type="submit" value="Neue Verwendung generieren" />
			</form>';
		}
	}
}
echo '</div></div></div></body></html>';
?>
