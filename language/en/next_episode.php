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
	'NEXT'	=> 'Next',
	'NEXT_EPISODE'	=> 'Next episode',
	'NEXT_EPISODE_CHECK'	=> 'checking for subs for %s',
	'NEXT_EPISODE_END'	=> 'check for next episode finished',
	'NEXT_EPISODE_START'	=> 'starting check for next episode',
));

?>