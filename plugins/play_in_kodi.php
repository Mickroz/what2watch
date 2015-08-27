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

// Should be named exactly like filename
$play_in_kodi_name = 'Play in Kodi';
$play_in_kodi_version = '1.0.2';

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
	global $user, $pass, $kodiIP, $kodiPort, $kodi_url, $log;
	
	if (!empty($kodiIP))
	{
		foreach ($data as $episode)
		{
			$buttons = array();
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
			$kodi = getUrl($kodi_url . urlencode($call), 'playInKodi');
			$result = json_decode($kodi, true);
		
			if ($result)
			{
				foreach ($result['result']['episodes'] as $episodes)
				{
					if ($show_name == $episodes['showtitle'])
					{
						$path = $episodes['file'];
	
						//Play a single video from file. change everything in bold.
						$link = urlencode('{"jsonrpc":"2.0","id":"1","method":"Player.Open","params":{"item":{"file":"' . $path . '"}}}');
						// Add to the buttons array
						$myurl = basename($_SERVER['PHP_SELF']) . "?" . $_SERVER['QUERY_STRING'];
						$buttons[] = ' <a href="' . $myurl . '&play=' . $key . '&kodi_link=' . $link . '"><i class="fa fa-play"></i> Play in Kodi</a>';
					}
				}
				// Set the new buttons array in the data array
				$data[$key]['hook_before_checkin'] = $buttons;
				unset($buttons, $result);
			}
		}
	}
	else
	{
		$log->error('playInKodi', 'Settings are empty!');
	}
	return $data;
}

if ($play_in_kodi)
{
	$tag = "kodi";
	$kodi_link = $_GET['kodi_link'];
	$log->info($tag, "Opening URL " . $kodi_url . $kodi_link);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $kodi_url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'What2Watch');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $kodi_link);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		'Content-Type: application/json',                                                                                
		'Content-Length: ' . strlen($kodi_link))                                                                       
	);                                                                                                                   

	$play = curl_exec($ch);
	curl_close($ch);
	$return = json_decode($play, true);
	if ($return['result'] == 'OK')
	{
		$error[] = 'Playing: ' . $data[$play_in_kodi]['message'];
		header("refresh:5; url=index.php?mode=shows");
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
