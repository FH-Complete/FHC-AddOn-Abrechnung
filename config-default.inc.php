<?php
// Kundennummer des Steuerberaters fuer CSV Export
define('KUNDENNUMMER','000');

// Hoechstbemessungsgrundlage
define('SV_HOECHSTBEMESSUNGSGRUNDLAGE',5130);

// Geringwertigkeitsgrenze
define('SV_GERINGWERTIG',438.05);

// Standard SV-Satz
define('SV_SATZ',0.1812);

// Altersabschlag
$cfg_sv_altersabschlag = array(56,0.03);

// Untergrenzen fuer abschlaege. Muss aufsteigend sortiert sein
$cfg_sv_abschlaege = array(
	array(1381,0.03),
	array(1506,0.02),
	array(1696,0.01)
);

// Untergrenzen fuer Berechnung der Lohnsteuer taeglich
$cfg_lsttgl = array(
	array(35.53,'0.00'),
	array(50.53,'0.25-8.883'),
	array(86.64,'0.35-13.937'),
	array(167.20,'0.42-20.002'),
	array(250.53,'0.48-30.034'),
	array(2778.31,'0.5-35.044'),
	array(99999999,'0.55-173.96')
);

