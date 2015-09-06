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
	'NEXT_EPISODE'	=> 'Volgende aflevering',
	'NEXT_EPISODE_START'	=> 'begin controle voor volgende aflevering',
	'NEXT_EPISODE_END'	=> 'controle voor volgende aflevering voltooid',
	'NEXT'	=> 'Volgende',
));

?>