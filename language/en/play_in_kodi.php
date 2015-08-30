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
));

?>