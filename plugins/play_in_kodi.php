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
// Should be named exactly like filename
$play_in_kodi_name = 'Play in Kodi';
$play_in_kodi_version = '1.0.2';
$play_in_kodi['config'] = array(
	'user'		=> '',
	'pass'		=> '',
	'kodiIP'	=> '',
	'kodiPort'	=> '',
	'active'	=> true
);

// Grabbing config
if ($active_plugins = @file_get_contents("plugins/active.json"))
{
	$config = json_decode($active_plugins, true);
}

$user = (isset($config['play_in_kodi']['config']['user'])) ? $config['play_in_kodi']['config']['user'] : '';
$pass = (isset($config['play_in_kodi']['config']['pass'])) ? $config['play_in_kodi']['config']['pass'] : '';
$kodiIP = (isset($config['play_in_kodi']['config']['kodiIP'])) ? $config['play_in_kodi']['config']['kodiIP'] : '';
$kodiPort = (isset($config['play_in_kodi']['config']['kodiPort'])) ? $config['play_in_kodi']['config']['kodiPort'] : '';
$kodi_url = "http://$user:$pass@$kodiIP:$kodiPort/jsonrpc?request=";

$play_kodi = (isset($_GET['play'])) ? $_GET['play'] : '';

$log->info('plugins', 'play_in_kodi loaded');

register_filter('hook_before_checkin','kodi');

function kodi($data)
{
	global $user, $pass, $kodiIP, $kodiPort, $kodi_url, $log, $lang;
	
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
			$ep_tvdbid = $episode['tvdbid'];

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
				if (!isset($result['result']['episodes']))
				{
					$log->error('playInKodi', sprintf($lang['KODI_SHOWNAME_FAILED'], $show_name,  $kodi));
					continue;
				}
				foreach ($result['result']['episodes'] as $episodes)
				{
					if ($show_name == $episodes['showtitle'])
					{
						$path = $episodes['file'];
	
						//Play a single video from file. change everything in bold.
						$link = urlencode('{"jsonrpc":"2.0","id":"1","method":"Player.Open","params":{"item":{"file":"' . $path . '"}}}');
						// Add to the buttons array
						$myurl = basename($_SERVER['PHP_SELF']) . "?" . $_SERVER['QUERY_STRING'];
						$buttons[] = ' <a href="' . $myurl . '&play=' . $ep_tvdbid . '&kodi_link=' . $link . '">' . $lang['KODI_PLAY_TEXT'] . '</a>';
					}
				}
				// Set the new buttons array in the data array
				$data[$ep_tvdbid]['hook_before_checkin'] = $buttons;
				unset($buttons, $result);
			}
			else
			{
				$log->error('playInKodi', sprintf($lang['KODI_CONNECT_FAILED'], $kodiIP, $kodiPort));
				break;
			}
		}
	}
	else
	{
		$log->warning('playInKodi', $lang['KODI_SETTINGS_EMPTY']);
	}
	return $data;
}

if ($play_kodi)
{

	$tag = "kodi";
	$kodi_link = $_GET['kodi_link'];
	$log->info($tag, sprintf($lang['KODI_OPENING_URL'], $kodi_url . $kodi_link));
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
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$return = json_decode($play, true);
	if ($return['result'] == 'OK')
	{
		$call = '{
			"jsonrpc":"2.0",
			"method":"Player.GetItem",
			"params": { "properties": ["title", "album", "artist", "season", "episode", "duration", "showtitle", "tvshowid", "thumbnail", "file", "fanart", "streamdetails"], "playerid": 1 },
			"id" : "VideoGetItem"
		}';
		$kodi = getUrl($kodi_url . urlencode($call), 'playInKodi');
		$result = json_decode($kodi, true);
		if (!function_exists('getShow'))
		{
			include_once('includes/functions_show.php');
		}
		$show_id = getShow($play_kodi);
		$get_trakt_info = getTraktId($show_id[$play_kodi]['show_slug'], $result['result']['item']['season'], $result['result']['item']['episode'] + 1);
		$get_trakt_id = json_decode($get_trakt_info, true);
		
		$getnext[$play_kodi]['tvdbid'] = $play_kodi;
		$getnext[$play_kodi]['trakt_id'] = $get_trakt_id['ids']['trakt'];
		$getnext[$play_kodi]['show_name'] = $show_id[$play_kodi]['show_name'];
		$getnext[$play_kodi]['season'] = $get_trakt_id['season'];
		$getnext[$play_kodi]['episode'] = $get_trakt_id['number'];
		$getnext[$play_kodi]['episode_name'] = $get_trakt_id['title'];
		update_show($getnext);
		
		$playing = sprintf($lang['KODI_PLAYING'], $result['result']['item']['showtitle'] . ' ' . $result['result']['item']['season'] . 'x' . sprintf('%02d', $result['result']['item']['episode']) . ' ' . $result['result']['item']['title']);
		$redirect_url = "index.php?mode=shows";
		meta_refresh(3, $redirect_url);
		msg_handler($playing, 'SUCCESS', 'success');
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
