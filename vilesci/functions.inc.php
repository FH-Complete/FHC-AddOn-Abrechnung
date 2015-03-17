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
 * Rechnet alle Mitarbeiter des Monats auf einmal ab
 */
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

/**
 * Erstellt ein Dropdown mit den Abrechnungsmonaten
 */
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
