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
	'NEXT'	=> 'Volgende',
	'NEXT_EPISODE'	=> 'Volgende aflevering',
	'NEXT_EPISODE_CHECK'	=> 'controleren op ondertitel voor %s',
	'NEXT_EPISODE_END'	=> 'controle voor volgende aflevering voltooid',
	'NEXT_EPISODE_START'	=> 'begin controle voor volgende aflevering',
));

?>