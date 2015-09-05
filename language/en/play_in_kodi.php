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
	'PLAY_IN_KODI'	=> 'Play in Kodi',
	'USER'	=> 'Username',
	'PASS'	=> 'Password',
	'KODIIP'	=> 'Kodi IP',
	'KODIPORT'	=> 'Kodi Port',
	'KODI_SHOWNAME_FAILED'	=> 'Cannot find %s, Kodi returned: %s',
	'KODI_PLAY_TEXT'	=> '<i class="fa fa-play"></i> Play in Kodi',
	'KODI_CONNECT_FAILED'	=> 'Cannot connect to %s:%s',
	'KODI_SETTINGS_EMPTY'	=> 'Kodi settings are empty!',
	'KODI_PLAYING'			=> 'Playing: %s',
	'KODI_OPENING_URL'		=> 'Opening URL %s',
));

?>