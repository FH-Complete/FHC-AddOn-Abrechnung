<?php
// Kundennummer des Steuerberaters fuer CSV Export
define('KUNDENNUMMER','000');

// Hoechstbemessungsgrundlage
define('SV_HOECHSTBEMESSUNGSGRUNDLAGE',4440);

// Geringwertigkeitsgrenze
define('SV_GERINGWERTIG',386.80);

// Standard SV-Satz
define('SV_SATZ',0.1812);

// Altersabschlag
$cfg_sv_altersabschlag = array(56,0.03);

// Untergrenzen fuer abschlaege. Muss aufsteigend sortiert sein
$cfg_sv_abschlaege = array(
	array(1219,0.03),
	array(1330,0.02),
	array(1497,0.01)
);

// Untergrenzen fuer Berechnung der Lohnsteuer taeglich
$cfg_lsttgl = array(
	array(33.71,0.00),
	array(69.98,0.365-0.05/30),
	array(166.67,0.4321429+13.23),
	array(99999999,0.5+55.02)
);

