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
	global $uid;
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

		// Wenn der Mitarbeiter noch kein Konto hat wird eines erstellt
		$konto = new wawi_konto();
		$konto->getKontoPerson($mitarbeiter->person_id);
		if(count($konto->result)==0)
		{
			$konto = new wawi_konto();
			$konto->new = true;
			$konto->aktiv = true;
			$konto->insertamum = date('Y-m-d H:i:s');
			$konto->insertvon = 'abrechnungsaddon';
			$konto->beschreibung['German'] = $mitarbeiter->nachname.' '.$mitarbeiter->vorname;
			$konto->kurzbz = $mitarbeiter->kurzbz;
			$konto->person_id = $mitarbeiter->person_id;

			if($konto->save())
				echo '<br>Neues Konto fuer '.$mitarbeiter->nachname.' '.$mitarbeiter->vorname.' angelegt';
			else
				echo '<br><span class="error">Fehler beim Anlegen des Kontos fuer '.$mitarbeiter->nachname.' '.$mitarbeiter->vorname.':'.$konto->errormsg;
		}

		// BIS-Verwendung laden um Abrechnungszeitraum zu ermitteln
		if(($verwendung_obj = getVerwendung($username, $abrechnungsdatum))!==false)
		{
			if(!$abrechnung->exists($username, $abrechnungsdatum))
			{
				if(!$abrechnung->abrechnung($username, $abrechnungsdatum, $verwendung_obj))
				{
						echo '<br><span class="error">'.$row_user->vorname.' '.$row_user->nachname.' fehlgeschlagen:'.$abrechnung->errormsg.'</span>';
				}
				else
				{
					$abrechnung->loadVertragsAufteilung();
					if($abrechnung->saveAbrechnung())
					{
						echo '<br>'.$row_user->vorname.' '.$row_user->nachname.' erfolgreich abgerechnet';
					}
					else
						echo '<br><span class="error">'.$row_user->vorname.' '.$row_user->nachname.' fehlgeschalgen:'.$abrechnung->errormsg.'</span>';

				}
			}
			else
			{
				echo '<br>'.$row_user->vorname.' '.$row_user->nachname.': Dieser Monat wurde bereits abgerechnet';
			}

			if($abrechnung->abschlussNoetig($username, $abrechnungsdatum, $verwendung_obj))
			{
				// Abschluss durchfuehren
				$abrechnung->abschluss($username, $abrechnungsdatum, $verwendung_obj);

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
					echo '<br>'.$row_user->vorname.' '.$row_user->nachname.': Sonderzahlungsabschluss wurde durchgefuehrt';
				}
			}
		}
		else
		{
			echo '<br><span class="error">'.$row_user->vorname.' '.$row_user->nachname.': Es wurde keine passende Verwendung gefunden</span>';
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
					AND NOT EXISTS(SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz in('abgerechnet','storno'))
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
		echo '<td>'.(isset($row['brutto'])?number_format($row['brutto'],2):'').'</td>';
		echo '<td>'.(isset($row['netto'])?number_format($row['netto'],2):'').'</td>';
		echo '<td>';

		if(isset($row['aufteilung']))
		{
			foreach($row['aufteilung'] as $kst=>$row_kst)
			{

				$kostenstelle->load($kst);
				if(isset($row['brutto']) && $row['brutto']!=0)
					$prozent =$row_kst['brutto']/$row['brutto']*100;
				else
					$prozent='';
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
function printAbrechnungsmonatDropDown($abrechnungsmonat=null)
{
	global $monatsname;
	echo 'Abrechnungsmonat: <select name="abrechnungsmonat">';
	$jahr = date('Y');
	$monat = date('m');
	$dtnow = new DateTime();
	$dtago = $dtnow->sub(new DateInterval('P6M'));

	for($i=1;$i<=12;$i++)
	{
		$value = $dtago->format('m/Y');
		if(($value==$abrechnungsmonat && !is_null($abrechnungsmonat))
		|| ((is_null($abrechnungsmonat) || $abrechnungsmonat=='') && $i==6))
			$selected='selected';
		else
			$selected='';

		echo '<option value="'.$value.'" '.$selected.'>'.$monatsname[1][$dtago->format('n')-1].' '.$dtago->format('Y').'</option>'; //$monatsname[1][$i-1]
		$dtago->add(new DateInterval('P1M'));
	}

	echo '</select>';
}

/**
 * Erstellt ein Dropdown mit den Studiensemestern
 */
function printStudiensemesterDropDown($studiensemester_kurzbz)
{
	echo '
	Studiensemester: <select name="studiensemester_kurzbz">
	';

	$stsem = new studiensemester();
	$stsem->getAll();

	foreach($stsem->studiensemester as $row)
	{
		if($row->studiensemester_kurzbz == $studiensemester_kurzbz)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</option>';
	}

	echo '</select>';
}

/**
 * Generiert Verträge für alle noch nicht zugeordneten Lehraufträge
 */
function generateVertraege($studiensemester_kurzbz)
{
	global $uid;
	$errormsg='';

	// Falsch zugeordnete Vertraege entfernen
	$vertrag = new vertrag();
	$vertrag->getFalscheVertraege($studiensemester_kurzbz);

	foreach($vertrag->result as $row)
	{
		$abrechnung_obj = new abrechnung();
		$person_obj = new person();
		$person_obj->load($row->person_id);

		if(!$abrechnung_obj->isTeilabgerechnet($row->vertrag_id))
		{
			$vertrag_obj = new vertrag();
			if(!$vertrag_obj->delete($row->vertrag_id))
			{
				echo "<br>Fehler beim Löschen des Vertrags $row->bezeichnung von $person_obj->vorname $person_obj->nachname";
			}
			else
			{
				echo "<br>Vertrag $row->bezeichnung von $person_obj->vorname $person_obj->nachname wurde erfolgreich entfernt";
			}
		}
		else
		{
			echo "<br>Vertrag $row->bezeichnung von $person_obj->vorname $person_obj->nachname kann nicht gelöscht werden da dieser bereits Abgerechnet wurde";
		}
	}

	// Betraege korrigieren bei bestehenden Lehrauftraegen
	$vertrag = new vertrag();
	$vertrag->getFalscheBetraege($studiensemester_kurzbz);

	if(count($vertrag->result)>0)
	{
		foreach($vertrag->result as $row)
		{
			$person_obj = new person();
			$person_obj->load($row->person_id);

			$vertrag_obj = new vertrag();
			if($vertrag_obj->load($row->vertrag_id))
			{
				$vertrag_obj->betrag = $row->stundensatz*$row->semesterstunden;
				$vertrag_obj->updateamum = date('Y-m-d H:i:s');
				$vertrag_obj->updatevon='vertraggenerate';
				if($vertrag_obj->save())
					echo "<br>Vertrag $row->bezeichnung von $person_obj->vorname $person_obj->nachname Betrag korrigiert von $row->betrag auf $vertrag_obj->betrag";
				else
					echo "<br>Vertrag $row->bezeichnung von $person_obj->vorname $person_obj->nachname Betrag konnte nicht korrigiert werden:".$vertrag_obj->errormsg;
			}
			else
			{
				echo "<br>Vertrag $row->bezeichnung von $person_obj->vorname $person_obj->nachname Betrag konnte nicht korrigiert werden:".$vertrag_obj->errormsg;
			}
		}
	}

	// Neue Vertraege erstellen
	$vertrag_person = new vertrag();
	if($vertrag_person->loadPersonenNichtZugeordnet($studiensemester_kurzbz))
	{
		foreach($vertrag_person->result as $row_person)
		{
			$vertrag_detail = new vertrag();
			if($vertrag_detail->loadNichtZugeordnet($row_person->person_id))
			{
				foreach($vertrag_detail->result as $row_detail)
				{
					if($row_detail->betrag=='' || $row_detail->betrag==0)
						continue;

					// Wenn das Studiensemester nicht passt dann ueberspringen da sonst alte Vertraege angelegt werden
					if($row_detail->studiensemester_kurzbz!='' && $row_detail->studiensemester_kurzbz!=$studiensemester_kurzbz)
						continue;

					echo '<br>Erstelle Vertrag für '.$row_person->vorname.' '.$row_person->nachname.' ('.$row_detail->type.') € '.number_format($row_detail->betrag,2,',','.');
					flush();
					ob_flush();
					$vertrag = new vertrag();
					$vertrag->person_id = $row_person->person_id;
					$vertrag->inservon = $uid;
					$vertrag->insertamum = date('Y-m-d H:i:s');
					$neu = true;
					$vertrag->vertragstyp_kurzbz=$row_detail->type;
					$vertrag->betrag=$row_detail->betrag;
					$vertrag->bezeichnung = $row_detail->bezeichnung;
					$vertrag->anmerkung ='';
					$vertrag->vertragsdatum = date('Y-m-d');

					if($vertrag->save())
					{
						$vertrag_id = $vertrag->vertrag_id;

						// Vertragselemente zuordnen

						switch($row_detail->type)
						{
							case 'Lehrauftrag':
								$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
								if($lehreinheitmitarbeiter->load($row_detail->lehreinheit_id, $row_detail->mitarbeiter_uid))
								{
									$lehreinheitmitarbeiter->vertrag_id=$vertrag_id;
									if(!$lehreinheitmitarbeiter->save())
										$errormsg.=$lehreinheitmitarbeiter->errormsg;
								}
								else
									$errormsg.=$lehreinheitmitarbeiter->errormsg;

								break;
							case 'Pruefung':
								$pruefung = new pruefung();
								if($pruefung->load($row_detail->pruefung_id))
								{
									$pruefung->vertrag_id=$vertrag_id;
									if(!$pruefung->save())
										$errormsg.=$pruefung->errormsg;
								}
								else
									$errormsg.=$pruefung->errormsg;
								break;
							case 'Betreuung':
								$projektbetreuer = new projektbetreuer();
								if($projektbetreuer->load($row_person->person_id, $row_detail->projektarbeit_id, $row_detail->betreuerart_kurzbz))
								{
									$projektbetreuer->vertrag_id=$vertrag_id;
									if(!$projektbetreuer->save())
										$errormsg.=$projektbetreuer->errormsg;
								}
								else
									$errormsg.=$projektbetreuer->errormsg;
								break;
							default:
								$errormsg.='Unknown type '.$type;
								break;
						}

						// Neu Status setzen
						$vertrag = new vertrag();

						$vertrag->vertrag_id = $vertrag_id;
						$vertrag->vertragsstatus_kurzbz = 'neu';
						$vertrag->datum = date('Y-m-d H:i:s');
						$vertrag->uid = $uid;

						if(!$vertrag->saveVertragsstatus(true))
							$errormsg.=$vertrag->erromsg;
					}
				}
			}
		}
	}
}

/**
 * Zeigt alle Personen an die Lehraufträge haben die noch keinem Vertrag zugeordnet sind
 */
function showFehlendeVertraege($studiensemester_kurzbz)
{
	$db =new basis_db();
	if($studiensemester_kurzbz=='')
	{
		$stsem = new studiensemester();
		$studiensemester_kurzbz=$stsem->getaktorNext();
	}
	echo '<form action="abrechnung.php" method="GET">
	<input type="hidden" name="work" value="generateVertraege" />';
	printStudiensemesterDropDown($studiensemester_kurzbz);
	echo '<input type="submit" value="Auswahl" />';
	echo '</form>';


	// Vertraege holen die falschen Personen zugeordnet sind
	$vertrag = new vertrag();
	$vertrag->getFalscheVertraege($studiensemester_kurzbz);

	if(count($vertrag->result)>0)
	{
		echo count($vertrag->result).' Verträge sind falschen Personen zugeordnet und werden entfernt<br>';
	}

	// Vertraege holen die falschen Personen zugeordnet sind
	$vertrag = new vertrag();
	$vertrag->getFalscheBetraege($studiensemester_kurzbz);

	if(count($vertrag->result)>0)
	{
		echo count($vertrag->result).' Verträge haben falsche Beträge und werden korrigiert<br>';
	}

	// Vertraege holen die falsche Beträge haben

	$vertrag = new vertrag();
	if($vertrag->loadPersonenNichtZugeordnet($studiensemester_kurzbz))
	{
		echo 'Folgende '.count($vertrag->result).' Personen haben Lehraufträge ohne Vertrag:';
		echo '
			<script>
				$(document).ready(function()
				{
					$("#tablevertraege").tablesorter(
					{
						sortList: [[1,0]],
						widgets: ["zebra"]
					});
				});
			</script>';

		echo '<table id="tablevertraege" class="tablesorter">
			<thead>
			<tr>
				<th>Vorname</th>
				<th>Nachname</th>
				<th>SVNR</th>
				<th>Anzahl der Verträge</th>
				<th>Gesamtbetrag der Verträge</th>
			</tr>
			</thead>
			<tbody>
			';
		foreach($vertrag->result as $row)
		{
			echo '<tr>
			<td>'.$row->vorname.'</td>
			<td>'.$row->nachname.'</td>
			<td>'.$row->svnr.'</td>';
			$vertrag_detail = new vertrag();
			$vertrag_detail->loadNichtZugeordnet($row->person_id);
			$gesamtbetrag=0;
			$anzahl_vertraege=0;
			foreach($vertrag_detail->result as $row_detail)
			{
				if($row_detail->studiensemester_kurzbz==$studiensemester_kurzbz)
				{
					$anzahl_vertraege++;
					$gesamtbetrag+=$row_detail->betrag;
				}
			}
			echo '<td align="center">'.$anzahl_vertraege.'</td>';
			echo '<td align="right">'.number_format($gesamtbetrag,2,',','.').'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
	echo '<form action="abrechnung.php?work=generateVertraege&studiensemester_kurzbz='.urlencode($studiensemester_kurzbz).'" method="POST">
	<input type="hidden" name="work" value="generateVertraege" />
	<input type="hidden" name="do" value="generate" />
	<input type="submit" value="Verträge generieren" />
	</form>';
}

/**
 * Generiert Verwendungen für alle Personen die Lehraufträge haben jedoch keine gültige Verwendung
 */
function generateVerwendung($studiensemester_kurzbz)
{
	global $db;

	$studiensemester = new studiensemester();
	$studiensemester->load($studiensemester_kurzbz);

	$qry = "SELECT
				distinct tbl_person.person_id, tbl_benutzer.uid, tbl_person.vorname, tbl_person.nachname
			FROM
				lehre.tbl_vertrag
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_benutzer USING(person_id)
				JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			WHERE
				tbl_vertrag.vertragstyp_kurzbz='Lehrauftrag'
				AND NOT EXISTS(SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus
					WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz in('abgerechnet','storno'))
				AND NOT EXISTS(SELECT 1 FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid
					AND beginn>=".$db->db_add_param($studiensemester->start)."
					AND ende<=".$db->db_add_param($studiensemester->ende)."
					)";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if(($errormsg=generateVerwendungMitarbeiter($row->uid, $studiensemester_kurzbz))===true)
				echo "<br>Verwendung angelegt für $row->vorname $row->nachname";
			else
				echo "<br>Fehler bei $row->vorname $row->nachname:".$errormsg;
			flush();
			ob_flush();
		}
	}
}

/**
 * Zeigt Personen an die keine gültige Verwendung haben obwohl Lehraufträge vorhanden sind
 * @param $studiensemester_kurzbz
 */
function showFehlendeVerwendung($studiensemester_kurzbz)
{
	if($studiensemester_kurzbz=='')
	{
		$stsem = new studiensemester();
		$studiensemester_kurzbz=$stsem->getaktorNext();
	}
	echo '<form action="abrechnung.php" method="GET">
	<input type="hidden" name="work" value="generateVerwendungAll" />';
	printStudiensemesterDropDown($studiensemester_kurzbz);
	echo '<input type="submit" value="Auswahl" />';
	echo '</form>';

	$studiensemester = new studiensemester();
	$studiensemester->load($studiensemester_kurzbz);

	$db = new basis_db();
	// Alle Personen holen die Verträge haben aber keine BIS-Verwendung
	$qry = "SELECT
				person_id, uid, vorname, nachname, svnr, min(startdatum) as startdatum,
				(SELECT dv_art FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=b.uid ORDER BY ende DESC limit 1) as dv_art,
				(SELECT habilitation FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=b.uid ORDER BY ende DESC limit 1) as habilitation
			FROM
			(SELECT *,
				(SELECT
					min(datum)
				FROM
					lehre.tbl_stundenplan
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				WHERE
					tbl_lehreinheitmitarbeiter.vertrag_id=a.vertrag_id) as startdatum
				FROM
					(
						SELECT
							distinct tbl_person.person_id, tbl_benutzer.uid,
							tbl_person.vorname, tbl_person.nachname,
							tbl_person.svnr, vertrag_id
						FROM
							lehre.tbl_vertrag
							JOIN public.tbl_person USING(person_id)
							JOIN public.tbl_benutzer USING(person_id)
							JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
						WHERE
							NOT EXISTS(SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus
							WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz in('abgerechnet','storno'))
							AND NOT EXISTS(SELECT 1 FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid
							AND beginn>=".$db->db_add_param($studiensemester->start)."
							AND ende<=".$db->db_add_param($studiensemester->ende)."
							)
					) a
			) as b
			GROUP BY person_id , uid, vorname, nachname, svnr";

	if($result = $db->db_query($qry))
	{
		echo 'Erstellen Sie zuerst alle Verträge bevor die Verwendungen generiert werden!<br>';
		echo 'Folgende '.$db->db_num_rows($result).' Personen haben Verträge aber keine gültige Verwendung:';
		echo '
			<script>
				$(document).ready(function()
				{
					$("#tableverwendung").tablesorter(
					{
						sortList: [[1,0]],
						widgets: ["zebra"]
					});
				});
			</script>';

		echo '<table id="tableverwendung" class="tablesorter">
			<thead>
			<tr>
				<th>Vorname</th>
				<th>Nachname</th>
				<th>SVNR</th>
				<th>Start</th>
				<th>Ende</th>
				<th>DV Art</th>
				<th>Habilitation</th>
			</tr>
			</thead>
			<tbody>
			';
		while($row = $db->db_fetch_object($result))
		{
			echo '
			<tr>
				<td>'.$row->vorname.'</td>
				<td>'.$row->nachname.'</td>
				<td>'.$row->svnr.'</td>
				<td>'.$row->startdatum.'</td>
				<td>'.$studiensemester->ende.'</td>
				<td>'.$row->dv_art.'</td>
				<td>'.($db->db_parse_bool($row->habilitation)?'Ja':'Nein').'</td>
			</tr>';
		}
		echo '</tbody></table>';
		echo '<form action="abrechnung.php?work=generateVerwendungAll&studiensemester_kurzbz='.urlencode($studiensemester_kurzbz).'" method="POST">
		<input type="hidden" name="do" value="generate" />
		<input type="submit" value="Verwendungen generieren" />
		</form>';
	}
}

/**
 * Generiert eine neue Verwendung wenn keine Vorhanden ist.
 * Dazu wird aus dem LVPlan die erste Stunde ermittelt.
 * Dies ist das Startdatum der Verwendung
 * Die Verwendung geht bis Ende des Semesters
 */
function generateVerwendungMitarbeiter($username, $studiensemester_kurzbz=null)
{
	global $uid, $db;

	$mitarbeiter = new mitarbeiter();
	if(!$mitarbeiter->load($username))
		return $mitarbeiter->errormsg;

	$startdatum = getVertragsStartDatum($mitarbeiter->person_id);

	$stsem_obj = new studiensemester();

	if($studiensemester_kurzbz=='' && $startdatum!='')
	{
		$stsem = $stsem_obj->getSemesterFromDatum($startdatum);
		$stsem_obj->load($stsem);
	}
	else
		$stsem_obj->load($studiensemester_kurzbz);

	// Wenn das Startdatum nicht aus dem LVPlan ermittelt werden kann dann wird
	// der Semesterstart genommen
	if($startdatum=='')
		$startdatum = $stsem_obj->start;

	$endedatum = $stsem_obj->ende;

	if($startdatum<=$endedatum)
	{
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
		$bisverwendung->beschausmasscode = ($bisverwendung_old->beschausmasscode!=''?$bisverwendung_old->beschausmasscode:2); // 0-15
		$bisverwendung->hauptberufcode = $bisverwendung_old->hauptberufcode;
		$bisverwendung->hauptberuflich = $bisverwendung_old->hauptberuflich;
		$bisverwendung->habilitation = $bisverwendung_old->habilitation;
		$bisverwendung->vertragsstunden = $bisverwendung->vertragsstunden;
		$bisverwendung->insertamum = date('Y-m-d H:i:s');
		$bisverwendung->insertvon = $uid;

		if(!$bisverwendung->save(true))
		{
			return 'Fehlgeschlagen: '.$bisverwendung->errormsg;
		}
		else
			return true;
	}
	else
	{
		return 'Fehlgeschlagen: Startdatum liegt nach dem Endedatum';
	}
}

/**
 * Uebersichtsliste ueber alle die Abgerechnet werden muessen
 *
 */
function showNochNichtAbgerechnet($abrechnungsmonat)
{
	global $db;

	echo '<form action="abrechnung.php" method="GET">
	<input type="hidden" name="work" value="nochnichtabgerechnet" />';
	printAbrechnungsmonatDropDown($abrechnungsmonat);
	echo '<input type="submit" value="Anzeigen" />
		</form>';

	// Liste aller Mitarbeiter die noch nicht abgerechnet wurden anzeigen
	$abrechnung = new abrechnung();
	$abrechnung->loadMitarbeiterUnabgerechnet();
	echo '<br>Bei den folgenden '.count($abrechnung->result).' Mitarbeitern sind noch nicht abgerechnete Verträge eingetragen:';

	echo '<form action="abrechnung.php?abrechnungsmonat='.$abrechnungsmonat.'&work=alleabrechnen" method="POST">
	<input type="hidden" name="abrechnungsmonat" value="'.$abrechnungsmonat.'" />
		<input type="submit" value="Alle abrechnen" />
		</form>';

	echo '
	<script>
		$(document).ready(function()
		{
			$("#tab_personen").tablesorter(
			{
				sortList: [[2,0]],
				widgets: ["zebra"]
			});
		});
	</script>

	<table id="tab_personen" class="tablesorter">
		<thead>
			<th></th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Letzte Abrechnung</th>
			<th>Beginn</th>
			<th>Ende</th>
			<th>lfdBrutto</th>
			<th>Tage abzurechnen</th>
			<th>Tage ausbezahlt</th>
			<th>Gesamthonorar</th>
			<th>Bisher ausbezahlt</th>
			<th>Offen</th>
		</thead><tbody>';
	$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
	$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
	$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));

	$datum_obj = new datum();

	foreach($abrechnung->result as $row)
	{
		echo '<tr>';
		echo '<td><a href="abrechnung.php?username='.$db->convert_html_chars($row->uid).'&abrechnungsmonat='.$abrechnungsmonat.'">Anzeigen</a></td>';
		echo '<td>'.$db->convert_html_chars($row->vorname).'</td>';
		echo '<td>'.$db->convert_html_chars($row->nachname).'</td>';
		echo '<td>'.$datum_obj->formatDatum($row->letzteabrechnung,'d.m.Y').'</td>';


		$abrechnung_detail = new abrechnung();
		if(($verwendung_obj = getVerwendung($row->uid, $abrechnungsdatum))!==false)
		{
			echo '<td align="center">'.$datum_obj->formatDatum($verwendung_obj->beginn,'d.m.Y').'</td>';
			echo '<td align="center">'.$datum_obj->formatDatum($verwendung_obj->ende,'d.m.Y').'</td>';

			if($abrechnungsdatum>$row->letzteabrechnung)
			{
				$abrechnung_detail->abrechnung($row->uid, $abrechnungsdatum, $verwendung_obj);

				echo '<td align="right">'.$db->convert_html_chars(number_format($abrechnung_detail->brutto,2,',','.')).'</td>';
				echo '<td align="center">'.$db->convert_html_chars($abrechnung_detail->tageabzurechnen).'</td>';
				echo '<td align="center">'.$db->convert_html_chars($abrechnung_detail->tageausbezahlt).'</td>';
				echo '<td align="right">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_gesamt,2,',','.')).'</td>';
				echo '<td align="right">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_dgf,2,',','.')).'</td>';

				//Wenn offen > gesamt (zB wegen anwesenheitsabzug)
				//dann wird gesamt angezeigt
				if($abrechnung_detail->honorar_offen > $abrechnung_detail->honorar_gesamt)
					echo '<td align="right">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_gesamt,2,',','.')).'</td>';
				else
					echo '<td align="right">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_offen,2,',','.')).'</td>';
			}
			else
			{
				$abrechnung_detail->getAbrechnungMitarbeiter($row->uid,$abrechnungsdatum);

				echo '<td align="right" class="bereitsabgerechnet">'.$db->convert_html_chars(number_format($abrechnung_detail->brutto,2,',','.')).'</td>';
				echo '<td align="center" class="bereitsabgerechnet">'.$db->convert_html_chars($abrechnung_detail->sv_teiler).'</td>';
				echo '<td align="center" class="bereitsabgerechnet"></td>';
				echo '<td align="right" class="bereitsabgerechnet">'.$db->convert_html_chars(number_format(($abrechnung_detail->honorar_dgf+$abrechnung_detail->honorar_offen),2,',','.')).'</td>';
				echo '<td align="right" class="bereitsabgerechnet">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_dgf,2,',','.')).'</td>';

				//Wenn offen > gesamt (zB wegen anwesenheitsabzug)
				//dann wird gesamt angezeigt
				if($abrechnung_detail->honorar_offen > $abrechnung_detail->honorar_gesamt)
					echo '<td align="right" class="bereitsabgerechnet">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_gesamt,2,',','.')).'</td>';
				else
					echo '<td align="right" class="bereitsabgerechnet">'.$db->convert_html_chars(number_format($abrechnung_detail->honorar_offen,2,',','.')).'</td>';
			}
		}
		else
		{
			echo '<td colspan="8">Keine gütlige Verwendung für dieses Monat</td>';
		}

		echo '</tr>';
	}
	echo '</tbody>
	</table>';
}

/**
 * Zeigt eine Tabelle mit allen offenen Vertraegen
 */
function printVertragsuebersicht($person_id, $abrechnungsdatum)
{
	global $db;
	$vertrag = new vertrag();
	$vertrag->loadVertrag($person_id, false);

	if(count($vertrag->result)>0)
	{
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

		foreach($vertrag->result as $row)
		{
			if($row->vertragsdatum <= $abrechnungsdatum)
			{
				echo '<tr>';
				echo '<td>'.$db->convert_html_chars($row->bezeichnung).'</td>';
				echo '<td align="right">'.$db->convert_html_chars($row->betrag).'</td>';
				echo '<td align="right">'.$db->convert_html_chars($row->status).'</td>';
				echo '</tr>';
				$gesamtbetrag+=$row->betrag;
			}
		}
		echo '</tbody>
		<tfoot>
			<tr>
				<th>Gesamt</th>
				<th align="right">'.number_format($gesamtbetrag,2).'</th>
			</tr>
		</tfoot>
		</table>';
	}
	else
		echo '<br>Alle Verträge sind bereits abgerechnet<br>';
}

function getVerwendung($username, $abrechnungsdatum)
{
	// BIS-Verwendung laden um Abrechnungszeitraum zu ermitteln
	$bisverwendung = new bisverwendung();

	if($bisverwendung->getVerwendungDatum($username, $abrechnungsdatum))
	{
		if(count($bisverwendung->result)>0)
		{
			foreach($bisverwendung->result as $row)
			{
				if($row->beginn!='' && $row->ende!='' && in_array($row->verwendung_code,array(1,2)))
				{
					return $row;
				}
			}
			return false;
		}
		else
			return false;
	}
	else
		return false;
}
?>
