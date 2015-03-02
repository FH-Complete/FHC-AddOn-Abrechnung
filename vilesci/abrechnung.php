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
	if(date('m')==1)
		$abrechnungsmonat = ('12/'.(date('Y')-1));
	else		
		$abrechnungsmonat = ((date('m')-1).'/'.date('Y'));
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
				<li><a href="abrechnung.php">Mitarbeiter wechseln</a></li>
				<li><a href="abrechnung.php?work=nochnichtabgerechnet">Übersichtsliste</a></li>
				<li class="dropdown">
					<a href="#export" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Export <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu" data-name="data">
						<li><a href="abrechnung.php?work=csvexport">LV60-Export</a></li>
						<li><a href="../../../content/statistik/vertragsuebersicht.xls.php">SV-Export</a></li>
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

		// Liste aller Mitarbeiter die noch nicht abgerechnet wurden anzeigen
		$abrechnung = new abrechnung();
		$abrechnung->loadMitarbeiterUnabgerechnet();
		echo 'Bei den folgenden Mitarbeitern sind noch nicht abgerechnete Verträge eingetragen:';
		echo '
		<script>
			$(document).ready(function() 
			{ 
				$("#tab_personen").tablesorter(
				{
					sortList: [[3,0]],
					widgets: ["zebra"]
				});
			});
		</script>

		<table id="tab_personen" class="tablesorter">
			<thead>
				<th></th>
				<th>Nachname</th>
				<th>Vorname</th>
				<th>Letzte Abrechnung</th>
			</thead><tbody>';

		foreach($abrechnung->result as $row)
		{
			echo '<tr>';
			echo '<td><a href="abrechnung.php?username='.$db->convert_html_chars($row->uid).'">Anzeigen</a></td>';
			echo '<td>'.$db->convert_html_chars($row->nachname).'</td>';
			echo '<td>'.$db->convert_html_chars($row->vorname).'</td>';
			echo '<td>'.$db->convert_html_chars($row->letzteabrechnung).'</td>';
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		
		echo '<form action="abrechnung.php?abrechnungsmonat='.$abrechnungsmonat.'&work=alleabrechnen" method="POST">';
		printAbrechnungsmonatDropDown();

		echo '
			<input type="submit" value="Alle abrechnen" />
			</form>';
	}
	elseif($work=='alleabrechnen')
	{
		alleAbrechnen($abrechnungsmonat);
	}
	elseif($work=='csvexport')
	{
		echo '<h1 class="page-header">CSV Export</h1>
			Bitte wählen Sie das zu exportierende Monat:<br><br>
		<form action="csvexport.php">';
		printAbrechnungsmonatDropDown();
		echo '<input type="submit" value="Exportieren" />';
		echo '</form>';
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
		printAbrechnungsuebersicht($username, $studiensemester_kurzbz);
	}
	else
	{
		echo '<h1 class="page-header">Abrechnung</h1>';
		$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
		$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
		$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));	

		if($monat==12)
			$next = '1/'.($jahr+1);
		else
			$next = ($monat+1).'/'.$jahr;

		if($monat==1)
			$prev = '12/'.($jahr-1);
		else
			$prev = ($monat-1).'/'.$jahr;

		echo '<div class="pull-left"><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$prev.'">&lt;&lt; vorheriges Monat</a></div>';
		echo '<div class="pull-right"><a href="abrechnung.php?username='.$username.'&abrechnungsmonat='.$next.'">nächstes Monat &gt;&gt;</a></div><br>';

		echo ' Abrechnungsmonat: '.$monatsname[1][$monat-1].' '.$jahr;

		if($work=='generateVerwendung')
		{
			// Generiert eine neue Verwendung wenn keine Vorhanden ist.
			// Dazu wird aus dem LVPlan die erste Stunde ermittelt.
			// Dies ist das Startdatum der Verwendung
			// Die Verwendung geht bis Ende des Semesters

			if($startdatum = getVertragsStartDatum($mitarbeiter->person_id))
			{
				$stsem_obj = new studiensemester();
				$stsem = $stsem_obj->getSemesterFromDatum($startdatum);
				$stsem_obj->load($stsem);
				$endedatum = $stsem_obj->ende;

				$bisverwendung_old = new bisverwendung();
				$bisverwendung_old->getLastVerwendung($username);

				$bisverwendung = new bisverwendung();

				$bisverwendung->beginn=$startdatum;
				$bisverwendung->ende=$endedatum;		
				$bisverwendung->ba1code=4; // Freier Dienstvertrag
				$bisverwendung->ba2code=1; // Befristet
				$bisverwendung->verwendung_code=1; // Lehr und Forschungspersonal 
				$bisverwendung->mitarbeiter_uid=$username;

				// Die restlichen Daten werden aus einer alten Verwendung geholt
				$bisverwendung->beschausmasscode = $bisverwendung_old->beschausmasscode;
				$bisverwendung->hauptberufcode = $bisverwendung_old->hauptberufcode;
				$bisverwendung->hauptberuflich = $bisverwendung_old->hauptberuflich;
				$bisverwendung->habilitation = $bisverwendung_old->habilitation;
				$bisverwendung->vertragsstunden = $bisverwendung->vertragsstunden;
				$bisverwendung->insertamum = date('Y-m-d H:i:s');
				$bisverwendung->insertvon = $uid;

				if(!$bisverwendung->save(true))
				{
					echo '<span class="error">Fehlgeschlagen: '.$bisverwendung->errormsg.'</span>';
				}
			}
			else
			{
				echo '<span class="error">Vertragsstart konnte nicht ermittelt werden.</span>';
			}
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
		$vertrag = new vertrag();
		$vertrag->loadVertrag($mitarbeiter->person_id, false);

		echo '
			<script>
				$(document).ready(function() 
				{ 
					$("#t1").tablesorter(
					{
						sortList: [[0,1]],
						widgets: ["zebra"]
					});
				});
			</script>
		
			<table id="t1" class="tablesorter" style="width:auto">
				<thead>
				<tr>
					<th>Vetrag</th>
					<th>Betrag</th>
					<th>Status</th>
				</tr>
				</thead><tbody>';

		$gesamtbetrag=0;
		$vertrag_arr=array();
		foreach($vertrag->result as $row)
		{
			echo '<tr>';
			echo '<td>'.$db->convert_html_chars($row->bezeichnung).'</td>';
			echo '<td align="right">'.$db->convert_html_chars($row->betrag).'</td>';
			echo '<td align="right">'.$db->convert_html_chars($row->status).'</td>';
			echo '</tr>';
			$gesamtbetrag+=$row->betrag;
			$vertrag_arr[]=$row->vertrag_id;
		}
		echo '</tbody>
		<tfoot>
			<tr>
				<th>Gesamt</th>
				<th align="right">'.number_format($gesamtbetrag,2).'</th>
			</tr>
		</tfoot>
		</table>';

		// BIS-Verwendung laden um Abrechnungszeitraum zu ermitteln
		$bisverwendung = new bisverwendung();

		if($bisverwendung->getVerwendungDatum($username, $abrechnungsdatum))
		{
			if(count($bisverwendung->result)>0)
			{
				$verwendungfound=false;
				foreach($bisverwendung->result as $row)
				{
					if($row->beginn!='' && $row->ende!='' && in_array($row->verwendung_code,array(1,2)))
					{
						$startdatum = $row->beginn;
						$endedatum = $row->ende;
						$bisverwendung_id = $row->bisverwendung_id;

						$verwendung_obj = $row;
						$verwendungfound=true;
						break;
					}
				}

				if($verwendungfound)
				{
					$abrechnung=new abrechnung();

					if(!$abrechnung->exists($username, $abrechnungsdatum))
					{
						if(!$abrechnung->abrechnung($username, $abrechnungsdatum, $gesamtbetrag, $verwendung_obj, $vertrag_arr))
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
								$abrechnung->loadVertragsAufteilung($vertrag_arr, $abrechnungsmonat);

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
							// Anwesenheiten pruefen
							// Monatssechstel ausbezahlen
							// Vertraege auf abgerechnet setzen
							$abrechnung->abschluss($username, $abrechnungsdatum, $gesamtbetrag, $verwendung_obj, $vertrag_arr);
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
							$abrechnung->getAbrechnungMitarbeiter($username, $abrechnungsdatum);
							echo '<h2>Abrechnungsdetails</h2>';
							echo '<div class="abrechnungsdetails">';
							echo nl2br($abrechnung->log);
							echo '</div>';
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
			else
			{
				echo "Es wurde keine aktuelle Verwendung für diesen Abrechnungszeitpunkt gefunden";
			}
		}
	}
}

function alleAbrechnen($abrechnungsmonat)
{

	$abrechnung_user = new abrechnung();
	$abrechnung_user->loadMitarbeiterUnabgerechnet();

	$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
	$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
	$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));	

	foreach($abrechnung_user->result as $row_user)
	{
		$username = $row_user->uid;

		$abrechnung=new abrechnung();

		$mitarbeiter = new mitarbeiter();
		$mitarbeiter->load($username);

		// Alle noch nicht abgerechneten Verträge anzeigen
		$vertrag = new vertrag();
		$vertrag->loadVertrag($mitarbeiter->person_id, false);

		$gesamtbetrag=0;
		$vertrag_arr=array();
		foreach($vertrag->result as $row)
		{
			$gesamtbetrag+=$row->betrag;
			$vertrag_arr[]=$row->vertrag_id;
		}

		// BIS-Verwendung laden um Abrechnungszeitraum zu ermitteln
		$bisverwendung = new bisverwendung();

		if($bisverwendung->getVerwendungDatum($username, $abrechnungsdatum))
		{
			if(count($bisverwendung->result)>0)
			{
				$verwendungfound=false;
				foreach($bisverwendung->result as $row)
				{
					if($row->beginn!='' && $row->ende!='' && in_array($row->verwendung_code,array(1,2)))
					{
						$startdatum = $row->beginn;
						$endedatum = $row->ende;
						$bisverwendung_id = $row->bisverwendung_id;

						$verwendung_obj = $row;
						$verwendungfound=true;
						break;
					}
				}

				if($verwendungfound)
				{
					if(!$abrechnung->exists($username, $abrechnungsdatum))
					{
						if(!$abrechnung->abrechnung($username, $abrechnungsdatum, $gesamtbetrag, $verwendung_obj))
						{
								echo '<br><span class="error">'.$username.' fehlgeschlagen:'.$abrechnung->errormsg.'</span>';
						}
						else
						{
							$abrechnung->loadVertragsAufteilung($vertrag_arr, $abrechnungsmonat);
							if($abrechnung->saveAbrechnung())
							{
								echo '<br>'.$username.' erfolgreich abgerechnet';
							}
							else
								echo '<br><span class="error">'.$username.' fehlgeschalgen:'.$abrechnung->errormsg.'</span>';
						
						}
					}
					else
					{
						echo '<br>'.$username.': Dieser Monat wurde bereits abgerechnet';
					}
				}
				else
				{
					echo '<br><span class="error">'.$username.': Es wurde keine passende Verwendung gefunden</span>';
				}
			}
			else
			{
				echo '<br><span class="error">'.$username.': Es wurde keine passende Verwendung gefunden</span>';
			}
		}
		else
		{
			echo '<br><span class="error">'.$username.': Es wurde keine passende Verwendung gefunden</span>';
		}
	}
}

/**
 * Berechnet das Datum an dem die Person angemeldet werden soll
 */
function getVertragsStartDatum($person_id)
{
	global $db;

	$qry = "SELECT 
				datum 
			FROM 
				lehre.tbl_stundenplan
			WHERE
				lehreinheit_id in(
				SELECT 	
					lehreinheit_id
				FROM 
					lehre.tbl_vertrag
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(vertrag_id)
				WHERE
					tbl_vertrag.person_id=".$db->db_add_param($person_id, FHC_INTEGER)."
					AND NOT EXISTS(SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz='abgerechnet')
			)
			ORDER BY datum LIMIT 1";

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			return $row->datum;
		}
	}
	return false;
}

echo " </div>
      </div>
    </div>";
echo '</body>
</html>';

/***********************************************/

/**
 * Erstellt eine Uebersicht ueber die bereits abgerechneten Monate
 */
function printAbrechnungsuebersicht($username, $studiensemester_kurzbz=null)
{
	global $datum_obj;

	echo '<h1 class="page-header">Übersicht</h1>';

	if(is_null($studiensemester_kurzbz))
	{
		$stsem = new studiensemester();
		$studiensemester_kurzbz = $stsem->getaktorNext();
	}

	$abrechnung = new abrechnung();
	$abrechnung->getAbrechnungen($username, $studiensemester_kurzbz);
	echo 'Studiensemester:'.$studiensemester_kurzbz;
	echo '
		<script>
			$(document).ready(function() 
			{ 
				$("#t1").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>';

	echo '<table class="tablesorter" id="t1">
		<thead>
			<tr>
				<th>Abrechnungsdatum</th>
				<th>Brutto ausbezahlt</th>
				<th>Netto ausbezahlt</th>
				<th>Aufteilung</th>
			</tr>
		</thead>
		<tbody>';
	$summe_brutto=0;
	$summe_netto=0;
	$abrechnung_data=array();
	foreach($abrechnung->result as $row)
	{
		if($row->kostenstelle_id=='')
		{
			$abrechnung_data[$row->abrechnungsdatum]['brutto']=$row->brutto;
			$abrechnung_data[$row->abrechnungsdatum]['netto']=$row->netto;
			$summe_brutto+=$row->brutto;
			$summe_netto+=$row->netto;
		}
		else
		{
			$abrechnung_data[$row->abrechnungsdatum]['aufteilung'][$row->kostenstelle_id]['brutto']=$row->brutto;
			$abrechnung_data[$row->abrechnungsdatum]['aufteilung'][$row->kostenstelle_id]['netto']=$row->netto;
		}		
	}

	$kostenstelle = new wawi_kostenstelle();
	
	foreach($abrechnung_data as $datum=>$row)
	{	
		echo '<tr>';
		echo '<td>'.$datum_obj->formatDatum($datum,'d.m.Y').'</td>';
		echo '<td>'.number_format($row['brutto'],2).'</td>';
		echo '<td>'.number_format($row['netto'],2).'</td>';
		echo '<td>';		
		
		if(isset($row['aufteilung']))
		{
			foreach($row['aufteilung'] as $kst=>$row_kst)
			{
					
				$kostenstelle->load($kst);
				$prozent =$row_kst['brutto']/$row['brutto']*100;
				echo $kostenstelle->bezeichnung;
				echo ' € '.number_format($row_kst['brutto'],2).' ('.$prozent.' %)';
				echo '<br />';
			}
		}
		echo '
		</td>
		</tr>';
	}
	echo '</tbody>
	<tfoot>
		<tr>
			<th>Gesamt</th>
			<th>'.number_format($summe_brutto,2).'</th>
			<th>'.number_format($summe_netto,2).'</th>
			<th></th>
		</tr>
	</tfoot>
	</table>';
}

function printAbrechnungsmonatDropDown()
{
	global $monatsname;
	echo '
	Abrechnungsmonat: <select name="abrechnungsmonat">
	';
	$jahr = date('Y');
	$monat = date('m');
	$dtnow = new DateTime();
	$dtago = $dtnow->sub(new DateInterval('P6M'));
	
	for($i=1;$i<=12;$i++)
	{
		$value = $dtago->format('m/Y');
		if($i==6)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$value.'" '.$selected.'>'.$monatsname[1][$dtago->format('n')-1].' '.$dtago->format('Y').'</option>'; //$monatsname[1][$i-1]
		$dtago->add(new DateInterval('P1M'));
	}

	echo '</select>';
}
?>
