<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/**
* @ignore
*/
if (!defined('IN_W2W'))
{
	exit;
}
include('common.php');
include('includes/functions_show.php');

$trakt = getProgress('hell-on-wheels', $trakt_token);
$progress = json_decode($trakt, true);
// We check here if the seasons list is empty, maybe the slug is incorrect
if ($progress['next_episode'] == '')
{
	echo "next_episode = ''<br />";
}
if (empty($progress['next_episode']))
{
	echo "next_episode = null";
}