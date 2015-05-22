<?php
/**
*
* @package What2Watch
* @author Mickroz
* @version Id$
* @link https://www.github.com/Mickroz/what2watch
* @copyright (c) 2015 Mickroz
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_W2W'))
{
	exit;
}
// Initial var setup
$user = '';
$pass = '';
$kodiIP = '';
$kodiPort = '';
$kodi_url = "http://$user:$pass@$kodiIP:$kodiPort/jsonrpc?request=";

$play_in_kodi = (isset($_GET['play'])) ? $_GET['play'] : '';

$log->info('plugins', 'play_in_kodi loaded');

register_filter('hook_before_checkin','kodi');

function kodi($data)
{
	global $user, $pass, $kodiIP, $kodiPort, $kodi_url;
	
	$buttons = array();
	foreach ($data as $episode)
	{
		// We check if there are already some buttons created
		if (array_key_exists('hook_before_checkin', $episode))
		{	
			$buttons = $episode['hook_before_checkin'];
		}
		$key = $episode['tvdbid'];

		$show_name = $episode['show_name'];
		$episode_name = $episode['name'];
		$call = '{
			"jsonrpc":"2.0",
			"method":"VideoLibrary.GetEpisodes",
			"params":{"sort": {"order": "ascending", "method": "title"}, "filter": {"and": [{"operator": "contains", "field": "title", "value": "' . $episode_name . '"}, {"operator": "contains", "field": "tvshow", "value": "' . $show_name . '"}]}, "properties": ["showtitle", "file"]},
			"id" : 1
		}';
		$kodi = getUrl($kodi_url . urlencode($call));
		$result = json_decode($kodi, true);
		
		foreach ($result['result']['episodes'] as $episodes)
		{
			if ($show_name == $episodes['showtitle'])
			{
				$path = $episodes['file'];
	
				//Play a single video from file. change everything in bold.
				$link = $kodi_url . urlencode('{"jsonrpc":"2.0","id":"1","method":"Player.Open","params":{"item":{"file":"' . $path . '"}}}');
				$data[$key]['kodi_link'] = $link;
				// Add to the buttons array
				$myurl = basename($_SERVER['PHP_SELF']) . "?" . $_SERVER['QUERY_STRING'];
				$buttons[] = ' <a href="' . $myurl . '&play=' . $key . '"><i class="fa fa-play"></i> Play in Kodi</a>';
			}
		}
		// Set the new buttons array in the data array
		$data[$key]['hook_before_checkin'] = $buttons;
		unset($buttons, $result);
	}
	return $data;
}

if ($play_in_kodi)
{
	$play = getUrl($data[$play_in_kodi]['kodi_link']);
	$return = json_decode($play, true);
	if ($return['result'] == 'OK')
	{
		$error[] = 'Playing: ' . $data[$play_in_kodi]['message'];
		header('Location: index.php?mode=shows');
	}
}
/**
* Movies, todo
*
* $call = '{
* "jsonrpc":"2.0",
* "method":"VideoLibrary.GetMovieDetails",
* "params":{"sort": {"order": "ascending", "method": "title"}, "filter": {"operator": "contains", "field": "title", "value": "Jupiter Ascending"}, "properties": ["file"]},
* "id" : 1
* }';
*
*/
?>
