<?php
/**
* DO NOT CHANGE
*/
if (!defined('IN_W2W'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'PLAY_IN_KODI'	=> 'Speel in Kodi',
	'USER'	=> 'Gebruikersnaam',
	'PASS'	=> 'Paswoord',
	'KODIIP'	=> 'Kodi IP',
	'KODIPORT'	=> 'Kodi Poort',
	'KODI_SHOWNAME_FAILED'	=> 'Kan %s niet vinden, Kodi zei: %s',
	'KODI_PLAY_TEXT'	=> '<i class="fa fa-play"></i> Speel in Kodi',
	'KODI_CONNECT_FAILED'	=> 'Kan niet verbinden met %s:%s',
	'KODI_SETTINGS_EMPTY'	=> 'Kodi instellingen zijn leeg!',
	'KODI_PLAYING'			=> 'Speelt nu: %s',
	'KODI_OPENING_URL'		=> 'Openen van URL %s',
));

?>