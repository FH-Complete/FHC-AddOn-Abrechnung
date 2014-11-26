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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

if (!$db = new basis_db())
	die('Datenbank kann nicht geoeffnet werden.  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
if(!$rechte->getBerechtigungen($uid))
	die('Sie haben keine Berechtigung fuer diese Seite');
		
if(!$rechte->isBerechtigt('addon/abrechnung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$errormsg=array();

$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:false);
$work=strtolower($work);

switch ($work)
{
	// Person - FH Technikum suche
	case 'mitarbeiter':
	 	$filter=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
		if (is_null($filter) || $filter=='')
			exit();
		
		$mitarbeiter=new mitarbeiter();
		if($mitarbeiter->search($filter))
		{
			$result=array();
		
			foreach($mitarbeiter->result as $row)
			{
				$item['uid']=html_entity_decode($row->uid);
				$item['vorname']=html_entity_decode($row->vorname);
				$item['nachname']=html_entity_decode($row->nachname);
				$item['titelpre']=html_entity_decode($row->titelpre);
				$item['titelpost']=html_entity_decode($row->titelpost);
				$result[]=$item;
			}
			echo json_encode($result);
		}

		break;

    default:
   		echo " Funktion $work fehlt! ";
		break;
}
?>
