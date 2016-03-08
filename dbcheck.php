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
 */
/**
 * FH-Complete Addon Template Datenbank Check
 *
 * Prueft und aktualisiert die Datenbank
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/addon'))
{
	exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

// Code fuer die Datenbankanpassungen

if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_abrechnung"))
{

	$qry = "CREATE TABLE addon.tbl_abrechnung
			(
				abrechnung_id bigint,
				mitarbeiter_uid varchar(32),
				kostenstelle_id integer,
				konto_id integer,
				abrechnungsdatum date,
				sv_lfd numeric(12,4),
				sv_satz numeric(8,4),
				sv_teiler integer,
				honorar_dgf numeric(12,4),
				honorar_offen numeric(12,4),
				honorar_extra numeric(12,4),
				brutto numeric(12,4),
				netto numeric(12,4),
				lst_lfd numeric(12,4),
				abzuege numeric(12,4)
			);

	ALTER TABLE addon.tbl_abrechnung ADD CONSTRAINT pk_addon_abrechnung PRIMARY KEY (abrechnung_id);

	CREATE SEQUENCE addon.seq_abrechnung_abrechnung_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

	ALTER TABLE addon.tbl_abrechnung ALTER COLUMN abrechnung_id SET DEFAULT nextval('addon.seq_abrechnung_abrechnung_id');
	ALTER TABLE addon.tbl_abrechnung ADD CONSTRAINT fk_mitarbeiter_abrechnung FOREIGN KEY (mitarbeiter_uid) REFERENCES public.tbl_mitarbeiter(mitarbeiter_uid) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE addon.tbl_abrechnung ADD CONSTRAINT fk_kostenstelle_abrechnung FOREIGN KEY (kostenstelle_id) REFERENCES wawi.tbl_kostenstelle(kostenstelle_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE addon.tbl_abrechnung ADD CONSTRAINT fk_konto_abrechnung FOREIGN KEY (konto_id) REFERENCES wawi.tbl_konto(konto_id) ON DELETE RESTRICT ON UPDATE CASCADE;

	GRANT SELECT, UPDATE, INSERT, DELETE on addon.tbl_abrechnung TO vilesci;
	GRANT SELECT, UPDATE ON addon.seq_abrechnung_abrechnung_id TO vilesci;

	INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('addon/abrechnung','Addon Abrechnung');
	";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung: Tabelle addon.tbl_abrechnung hinzugefuegt!<br>';

}

/// Nicht benoetigte Spalten entfernen
if($result = @$db->db_query("SELECT abzuege FROM addon.tbl_abrechnung"))
{

	$qry = "ALTER TABLE addon.tbl_abrechnung DROP COLUMN abzuege;
	ALTER TABLE addon.tbl_abrechnung DROP COLUMN honorar_extra;";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung: Spalte abzuege und honorar_extra entfernt!<br>';

}

if(!$result = @$db->db_query("SELECT log FROM addon.tbl_abrechnung"))
{

	$qry = "ALTER TABLE addon.tbl_abrechnung ADD COLUMN log text;";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung: Spalte log hinzugefuegt!<br>';

}

if(!$result = @$db->db_query("SELECT abschluss FROM addon.tbl_abrechnung"))
{

	$qry = "ALTER TABLE addon.tbl_abrechnung ADD COLUMN abschluss boolean;";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung: Spalte abschluss hinzugefuegt!<br>';

}

if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_abrechnung_kostenstelle"))
{

	$qry = "CREATE TABLE addon.tbl_abrechnung_kostenstelle
        (
        	abrechnung_kostenstelle_id bigint,
        	studiengang_kz integer,
        	orgform_kurzbz varchar(3),
        	sprache varchar(16),
        	kostenstelle_id bigint
        );


        CREATE SEQUENCE addon.seq_abrechnung_kostenstelle_id
         INCREMENT BY 1
         NO MAXVALUE
         NO MINVALUE
         CACHE 1;

        ALTER TABLE addon.tbl_abrechnung_kostenstelle ADD CONSTRAINT pk_abrechnung_kostenstelle PRIMARY KEY (abrechnung_kostenstelle_id);
        ALTER TABLE addon.tbl_abrechnung_kostenstelle ALTER COLUMN abrechnung_kostenstelle_id SET DEFAULT nextval('addon.seq_abrechnung_kostenstelle_id');

        ALTER TABLE addon.tbl_abrechnung_kostenstelle ADD CONSTRAINT fk_abrechnung_kostenstelle_studiengang_kz FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
        ALTER TABLE addon.tbl_abrechnung_kostenstelle ADD CONSTRAINT fk_abrechnung_kostenstelle_orgform FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
        ALTER TABLE addon.tbl_abrechnung_kostenstelle ADD CONSTRAINT fk_abrechnung_kostenstelle_sprache FOREIGN KEY (sprache) REFERENCES public.tbl_sprache (sprache) ON DELETE RESTRICT ON UPDATE CASCADE;
        ALTER TABLE addon.tbl_abrechnung_kostenstelle ADD CONSTRAINT fk_abrechnung_kostenstelle_kostenstelle FOREIGN KEY (kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE RESTRICT ON UPDATE CASCADE;

        GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_abrechnung_kostenstelle TO vilesci;";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung_kostenstelle: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung_kostenstelle: neue Tabelle hinzugefuegt!<br>';

}

// Berechtigungen fuer Web User erteilen
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_abrechnung' AND table_schema='addon' AND grantee='web' AND privilege_type='SELECT'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT SELECT ON addon.tbl_abrechnung TO web;
				GRANT SELECT ON campus.tbl_anwesenheit TO web;";

		if(!$db->db_query($qry))
			echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
		else
			echo 'addon.tbl_abrechnung / campus.tbl_anwesenheit: Leserechte fuer User web erteilt';
	}
}

if(!$result = @$db->db_query("SELECT importiert FROM addon.tbl_abrechnung"))
{
	$qry = "ALTER TABLE addon.tbl_abrechnung ADD COLUMN importiert boolean DEFAULT false;
    UPDATE addon.tbl_abrechnung SET importiert=true WHERE kostenstelle_id is null;";

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_abrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' addon.tbl_abrechnung: Spalte importiert hinzugefuegt!<br>';

}

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen=array(
	"addon.tbl_abrechnung"  => array("abrechnung_id","mitarbeiter_uid","kostenstelle_id","konto_id","abrechnungsdatum","sv_lfd","sv_satz","sv_teiler","honorar_dgf","honorar_offen","brutto","netto","lst_lfd","log","abschluss","importiert"),
    "addon.tbl_abrechnung_kostenstelle"  => array("abrechnung_kostenstelle_id","studiengang_kz","orgform_kurzbz","sprache","kostenstelle_id"),
);

$tabs=array_keys($tabellen);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}
?>
