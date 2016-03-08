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
 * CSV Import der Abrechnungsdaten
 *
 * Format:
 * Klnr;Pnr;Unr;Name;LST;SV;Brutto;SV-Beitrag;Lohnsteuer;Abzüge;Netto
 */

if (count(get_included_files()) == 1)
	die('Diese Datei sollte nicht direkt aufgerufen werden!');

$stsem_obj = new studiensemester();

$abrechnungsmonat = (isset($_REQUEST['abrechnungsmonat'])?$_REQUEST['abrechnungsmonat']:((date('m')-1).'/'.date('Y')));
$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));
$stsem = $stsem_obj->getSemesterFromDatum($abrechnungsdatum);

echo '
<h1 class="page-header">CSV - Import</h1>';
echo '<form action="abrechnung.php?work=csvimport" method="POST" enctype="multipart/form-data">
Bitte wählen Sie die CSV Datei für den Import aus:
<input type="file" name="csvdatei" />';

echo 'Abrechnungsmonat: <select name="abrechnungsmonat">';

$jahr = date('Y');
$monat = date('m');
$dtnow = new DateTime();
$dtago = $dtnow->sub(new DateInterval('P1M'));

for($i=1;$i<=3;$i++)
{
	$value = $dtago->format('m/Y');
	if(($value==$abrechnungsmonat && !is_null($abrechnungsmonat))
	|| ((is_null($abrechnungsmonat) || $abrechnungsmonat=='') && $i==3))
		$selected='selected';
	else
		$selected='';

	echo '<option value="'.$value.'" '.$selected.'>'.$monatsname[1][$dtago->format('n')-1].' '.$dtago->format('Y').'</option>'; //$monatsname[1][$i-1]
	$dtago->sub(new DateInterval('P1M'));
}

echo '</select>';
echo '<input type="submit" value="Importieren" />
</form>';

if(isset($_POST['abrechnungsmonat']))
{
	$abrechnungsmonat = $_POST['abrechnungsmonat'];

	$anzahl_korrigiert=0;
	$anzahl_fehler=0;
	$log='';

	if (is_uploaded_file($_FILES['csvdatei']['tmp_name']))
	{
		$handle = fopen ($_FILES['csvdatei']['tmp_name'],"r");
		$row=0;
		while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE )
		{
			$row++;
			// 1. Row = Ueberschrift -> wegwerfen
			if($row==1)
				continue;

			// Pruefen ob das CSV korrekte Spaltenanzahl hat
			if($row==2 && !isset($data[10]))
				die('CSV Datei hat falsche Spaltenanzahl -> Abbruch');

			// Letzte Zeile enthaelt Gesamtsumme und keine Personalnummer
			// Diese wird uebersprungen
			if($data[1]=='')
				continue;

			//Klnr;Pnr;Unr;Name;LST;SV;Brutto;SV-Beitrag;Lohnsteuer;Abzüge;Netto
			$mitarbeiter = new mitarbeiter();
			if($mitarbeiter->getMitarbeiterFromPersonalnummer($data[1]))
			{
				//$lst = $data[4];
				//$sv=$data[5];
				$brutto=str_replace(',','.',trim($data[6]));
				$svbeitrag = str_replace(',','.',trim($data[7]));
				$lohnsteuer = str_replace(',','.',trim($data[8]));
				$abzuege = str_replace(',','.',trim($data[9]));
				$netto = str_replace(',','.',trim($data[10]));
				$abrechnung = new abrechnung();
				if($abrechnung->getAbrechnungMitarbeiter($mitarbeiter->uid, $abrechnungsdatum))
				{
					$log.= "<br>Korrigiere $mitarbeiter->uid";
					$log.= "
					Brutto: $abrechnung->brutto -> $brutto ;
					Netto: $abrechnung->netto -> $netto ;
					SV: $abrechnung->sv_lfd -> $svbeitrag ;
					LSt: $abrechnung->lst_lfd -> $lohnsteuer ;";

					$abrechnung->new=false;
					$abrechnung->brutto = $brutto;
					$abrechnung->netto = $netto;
					$abrechnung->sv_lfd = $svbeitrag;
					$abrechnung->lst_lfd = $lohnsteuer;
					$abrechnung->importiert = true;
					if(!$abrechnung->save())
					{
						$anzahl_fehler++;
						$log.="Fehler beim Speichern $abrechnung->errormsg";
					}
					else
						$anzahl_korrigiert++;
				}
				else
				{
					$anzahl_fehler++;
					$log.= "<br>Es wurde keine Abrechnung gefunden für $mitarbeiter->vorname $mitarbeiter->nachname mit Datum $abrechnungsdatum";
				}
			}
			else
			{
				$anzahl_fehler++;
				$log.= "<br>Fehler: Kein Mitarbeiter gefunden mit Persoanlnummer ".$data[1].":".$data[3];
			}
		}
		fclose ($handle);

		echo '<br>Import abgeschlossen';
		echo '<br>Anzahl korrigierter Abrechnungen:<span class="ok">'.$anzahl_korrigiert.'</span>';
		if($anzahl_fehler>0)
			echo '<br>Anzahl Fehler:<span class="error">'.$anzahl_fehler.'</span>';

		echo '<hr>';
		echo $log;
	}
	else
	{
		echo 'File Upload failed';
	}
}


?>
