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
/*
 * Erstellt eine Abrechnungsübersicht für externe Lektoren
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/vertrag.class.php');
require_once('../../../include/stunde.class.php');
require_once('../include/abrechnung.class.php');

if (!$db = new basis_db())
  die('Fehler beim Oeffnen der Datenbankverbindung');

$summe = 0;
$user = get_uid();

if(!check_lektor($user))
    die('Diese Seite ist nur fuer Lektoren zugänglich');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['uid']) && ($rechte->isBerechtigt('admin') || $rechte->isBerechtigung('addon/abrechnung')))
    $user = $_GET['uid'];

$p = new phrasen();

$datum_obj = new datum();
$studiengang = new studiengang();
$studiengang->getAll(null, false);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?php echo $p->t('abrechnung/uebersicht'); ?></title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
    <link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css">
    <script src="../../../include/js/jquery1.9.min.js" type="text/javascript"></script>
    <script>
    $(document).ready(function()
    {
    	$("#termine").tablesorter(
    	{
    		sortList: [[1,0]],
    		widgets: ["zebra"]
    	});
        $("#lehrauftraege").tablesorter(
    	{
    		sortList: [[1,1]],
    		widgets: ["zebra"]
    	});
        $("#sonderhonorare").tablesorter(
    	{
    		sortList: [[0,0]],
    		widgets: ["zebra"]
    	});
    });
    </script>
<?php
$stsem = new studiensemester();
$studiensemester_kurzbz = $stsem->getNearest();
$abrechnungsmonat=filter_input(INPUT_GET,'abrechnungsmonat');

$mitarbeiter = new mitarbeiter();
$mitarbeiter->load($user);

echo '<H1>'.$p->t('abrechnung/uebersicht').' - '.$mitarbeiter->titelpre.' '.$mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$mitarbeiter->titelpost.'</H1>';

echo '
<form action="abrechnungsuebersicht.php" method="GET">
'.$p->t('abrechnung/abrechnungsmonat').': <select name="abrechnungsmonat">';
$jahr = date('Y');
$monat = date('m');
$dtnow = new DateTime();
$dtago = $dtnow->sub(new DateInterval('P6M'));

for($i=1;$i<=12;$i++)
{
    // aeltere Eintraege nicht angzeigen
    if($dtago->format('Y-m-d')>'2015-08-01')
    {
        $value = $dtago->format('m/Y');
        if($abrechnungsmonat=='')
            $abrechnungsmonat=$value;

        if(($value==$abrechnungsmonat && !is_null($abrechnungsmonat))
        || ((is_null($abrechnungsmonat) || $abrechnungsmonat=='') && $i==6))
            $selected='selected';
        else
            $selected='';

        echo '<option value="'.$value.'" '.$selected.'>'.$monatsname[1][$dtago->format('n')-1].' '.$dtago->format('Y').'</option>'; //$monatsname[1][$i-1]
    }
    $dtago->add(new DateInterval('P1M'));
}

echo '</select>
<input type="hidden" name="uid" value="'.$user.'" />
<input type="submit" value="anzeigen">
</form>';

$jahr = mb_substr($abrechnungsmonat, mb_strpos($abrechnungsmonat,'/')+1);
$monat = mb_substr($abrechnungsmonat,0,mb_strpos($abrechnungsmonat,'/'));
$abrechnungsdatum=date('Y-m-t',mktime(0,0,0,$monat,1, $jahr));

$bisverwendung = new bisverwendung();
$bisverwendung->getLastVerwendung($mitarbeiter->uid);

echo '<table border=0>
<tr>
    <td><b>'.$p->t('abrechnung/dvart').':</b></td>
    <td>'.$bisverwendung->dv_art.'</td>
    <td width="10px"></td>
    <td><b>'.$p->t('abrechnung/lektorencode').':</b></td>
    <td>'.$mitarbeiter->kurzbz.'</td>
</tr>
<tr>
    <td><b>'.$p->t('abrechnung/abrechnungszeitraum').':</b></td>
    <td>'.$datum_obj->formatDatum($bisverwendung->beginn,'d.m.Y').' - '.$datum_obj->formatDatum($bisverwendung->ende,'d.m.Y').'</td>
    <td width="10px"></td>
    <td><b>'.$p->t('abrechnung/personalnummer').':</b></td>
    <td>'.$mitarbeiter->personalnummer.'</td>
</tr>';

$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getSemesterFromDatum($abrechnungsdatum);
$stsem_obj->load($stsem);

$stunde = new stunde();
$stunde->loadAll();
$stunden_arr = array();
foreach($stunde->stunden as $row)
{
	$stunden_arr[$row->stunde]['von']=$row->beginn;
	$stunden_arr[$row->stunde]['bis']=$row->ende;
}

$vertrag = new vertrag();
$vertrag->getVertragFromDatum($mitarbeiter->uid, $abrechnungsdatum);

$sonderhonorar=array();
$lehrauftrag=array();
$gesamthonorar=0;
echo '<table border=0><tr><td valign="top">';
echo '<h2>'.$p->t('abrechnung/termine').'</h2>';
echo '<table id="termine" class="tablesorter">
    <thead>
        <tr>
            <th>'.$p->t('abrechnung/id').'</th>
            <th>'.$p->t('abrechnung/datum').'</th>
            <th>'.$p->t('abrechnung/von').'</th>
            <th>'.$p->t('abrechnung/bis').'</th>
            <th>'.$p->t('abrechnung/stunden').'</th>
            <th>'.$p->t('abrechnung/honorar').'</th>
            <th>'.$p->t('abrechnung/gehalten').'</th>
            <th>'.$p->t('abrechnung/vertrag').'</th>
        </tr>
    </thead>
    <tbody>';
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
							echo '<tr>
									<td>'.$row_detail->lehreinheit_id.'</td>
									<td>'.$datum_obj->formatDatum($datum,'d.m.Y').'</td>
									<td>'.$von->format('H:i').'</td>
									<td>'.$bis->format('H:i').'</td>
									<td>'.($row_stunde->bis-$row_stunde->von+1).'</td>
									<td>'.$lem->stundensatz.'</td>';

                            echo '<td>';
                            if($datum>date('Y-m-d'))
                            {
                                echo 'Nein';
                            }
                            else
                            {
                                $anwesenheit = new anwesenheit();
                                if($anwesenheit->AnwesenheitExists($row_detail->lehreinheit_id, $datum))
                                    echo 'Ja';
                                else
                                    echo 'Nein';
                            }
                            echo '</td>';
                            echo '
									<td>'.$row_vertrag->bezeichnung.'</td>';
							echo '</tr>';
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
    echo '</tbody></table>';
    echo '</td><td valign="top">';
    echo '<h2>'.$p->t('abrechnung/lehrauftraege').'</h2>';
    echo '<table id="lehrauftraege" class="tablesorter">
        <thead>
            <tr>
                <th>'.$p->t('abrechnung/id').'</th>
                <th>'.$p->t('abrechnung/bezeichnung').'</th>
                <th>'.$p->t('abrechnung/stundensatz').'</th>
                <th>'.$p->t('abrechnung/semesterstunden').'</th>
                <th>'.$p->t('abrechnung/gesamt').'</th>
            </tr>
        </thead>
        <tbody>';
	foreach($lehrauftrag as $row_lehrauftrag)
	{
		echo '<tr>
				<td>'.$row_lehrauftrag['lehreinheit_id'].'</td>
				<td>'.$row_lehrauftrag['bezeichnung'].'</td>
				<td>'.$row_lehrauftrag['stundensatz'].'</td>
				<td>'.number_format($row_lehrauftrag['semesterstunden'],1).'</td>
				<td>'.$row_lehrauftrag['gesamt'].'</td>
			</tr>';
        $gesamthonorar +=$row_lehrauftrag['gesamt'];
	}
    echo '</tbody></table>';

    if(count($sonderhonorar)>0)
    {
        echo '<h2>'.$p->t('abrechnung/sonderhonorare').'</h2>';
        echo '<table id="sonderhonorare" class="tablesorter">
            <thead>
                <tr>
                    <th>'.$p->t('abrechnung/datum').'</th>
                    <th>'.$p->t('abrechnung/bezeichnung').'</th>
                    <th>'.$p->t('abrechnung/gesamt').'</th>
                </tr>
            </thead>
            <tbody>';
    	foreach($sonderhonorar as $row_sonderhonorar)
    	{
    		echo '<tr>
    				<td>'.$datum_obj->formatDatum($row_sonderhonorar['datum'],'d.m.Y').'</td>
    				<td>'.$row_sonderhonorar['bezeichnung'].'</td>
    				<td>'.$row_sonderhonorar['gesamt'].'</td>
    			</tr>';
            $gesamthonorar +=$row_sonderhonorar['gesamt'];
    	}
        echo '</tbody></table>';
    }
    echo '<br><br><b>'.$p->t('abrechnung/gesamthonorar').':</b> € '.number_format($gesamthonorar,2,',','.');


    $abrechnung = new abrechnung();
    if($abrechnung->getAbrechnungMitarbeiter($user, $abrechnungsdatum))
    {
        echo '<h2>'.$p->t('abrechnung/abrechnungsdetails').'</h2>';

        echo '<table class="tablesorter" style="width:auto">
            <thead>
            <tr>
                <th>'.$p->t('abrechnung/datum').'</th>
                <th>'.$p->t('abrechnung/brutto').'</th>
                <th>'.$p->t('abrechnung/svlfd').'</th>
                <th>'.$p->t('abrechnung/lst').'</th>
                <th>'.$p->t('abrechnung/netto').'</th>
                <th>'.$p->t('abrechnung/sonderzahlung').'</th>
            </tr>
            </thead>
            <tbody>';
        echo '<tr>
                <td>'.$datum_obj->formatDatum($abrechnungsdatum,'d.m.Y').'</td>
                <td>'.number_format($abrechnung->brutto,2,',','.').'</td>
                <td>'.number_format($abrechnung->sv_lfd,2,',','.').'</td>
                <td>'.number_format($abrechnung->lst_lfd,2,',','.').'</td>
                <td>'.number_format($abrechnung->netto,2,',','.').'</td>
                <td>'.number_format($abrechnung->brutto/6,2,',','.').'</td>
            </tr>
            </body>
            </table>
            ';
    }
    echo '</td></tr></table>';
?>
</body>
</html>
