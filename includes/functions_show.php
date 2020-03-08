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

function trakt_show_cancel()
{
	global $trakt_token, $log, $lang, $error;
	
	$log->debug('trakt.tv', $lang['TRAKT_CANCEL']);
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/checkin");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer $trakt_token",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

function trakt_show_checkin($trakt_id, $message)
{
	global $trakt_token, $log, $lang, $error;
	
	$log->debug('trakt.tv', $lang['TRAKT_START']);
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/checkin");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_POST, TRUE);

	curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		\"episode\": {
		\"ids\": {
			\"trakt\": $trakt_id
		}
	},
	\"sharing\": {
		\"facebook\": true,
		\"twitter\": true,
		\"tumblr\": false
	},
	\"message\": \"$message\",
	\"app_version\": \"1.0\",
	\"app_date\": \"2015-03-18\"
	}");

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer $trakt_token",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

function getTraktId($slug, $season, $episode)
{
	global $trakt_token, $log, $lang, $error;
	
	$log->debug('getTraktId', sprintf($lang['TRAKT_GET_ID'], $slug, $season, $episode));
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/shows/$slug/seasons/$season/episodes/$episode");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

function getSeasons($slug, $trakt_token)
{
	global $log, $lang, $error;

	$tag = 'getSeasons';
	$log->debug($tag, sprintf($lang['TRAKT_GET_COLLECTED'], $slug));
	$log->debug($tag, "Opening URL https://api.trakt.tv/shows/$slug/seasons?extended=full");
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/shows/$slug/seasons?extended=full");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer $trakt_token",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	
	return $response;
}

function getCollected($slug, $trakt_token)
{
	global $log, $lang, $error;
	
	$tag = 'getCollected';
	$log->debug($tag, sprintf($lang['TRAKT_GET_COLLECTED'], $slug));
	$log->debug($tag, "Opening URL https://api.trakt.tv/shows/$slug/progress/collection");
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/shows/$slug/progress/collection");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer $trakt_token",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	
	return $response;
}

function getProgress($slug, $trakt_token)
{
	global $log, $lang, $error;
	
	$tag = 'getProgress';
	$log->debug($tag, sprintf($lang['TRAKT_GET_PROGRESS'], $slug));
	$log->debug($tag, "Opening URL https://api.trakt.tv/shows/$slug/progress/watched");
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/shows/$slug/progress/watched");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer $trakt_token",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	
	return $response;
}

function getShow($tvdbid)
{
	$tag = 'getShow';
	global $sickbeard, $sb_api, $log, $trakt_token, $lang, $error;
	
	$log->info($tag, sprintf($lang['SB_START'], $tvdbid));
	
	$show_id = getUrl($sickbeard . "/api/" . $sb_api . "/?cmd=show&tvdbid=" . $tvdbid, $tag);
	if (!$show_id)
	{
		$error[] = sprintf($lang['SB_NO_SHOW'], $tvdbid);
		$log->error($tag, sprintf($lang['SB_NO_SHOW'], $tvdbid));
	}
	$result_show = json_decode($show_id, true);
	$log->info($tag, sprintf($lang['SB_SHOW'], $result_show['data']['show_name']));
	// Checking which show actually has a episode downloaded
	// and put all  tvdb id's in an array
	// TODO grab naming pattern
	$season_list = $result_show['data']['season_list'];
	// We reverse the list for logging only
	$season_list = array_reverse($season_list);
	$show_name = array();
	$numItems = count($season_list);
	$i = 0;
	$s = 0;
	foreach ($season_list as $id => $season)
	{
		$padded = sprintf('%02d', $season); 
		$dir = $result_show['data']['location'] . "/Season " .  $padded;
		if (!file_exists($dir) && !is_dir($dir))
		{
			if(++$i === $numItems && $s == 0)
			{
				$log->info('getSeason', sprintf($lang['NO_SEASONS_FOUND'], $result_show['data']['show_name']));
			}
			continue;
		}
		$s++;
		$log->debug('getSeason', sprintf($lang['SEASONS_FOUND'], $padded, $result_show['data']['show_name']));
		$show_name[$tvdbid]['show_name'] = $result_show['data']['show_name'];
		$show_name[$tvdbid]['location'] = $result_show['data']['location'];
		//$show_name[$tvdbid]['tvrage_id'] = $result_show['data']['tvrage_id'];
	}
	if (isset($show_name[$tvdbid]['show_name']))
	{
		$slug = get_slug($tvdbid);
		if (empty($slug))
		{
			$slug = slugify($result_show['data']['show_name']);
		}
		$show_name[$tvdbid]['show_slug'] = $slug;
	}
	
	return $show_name;
}

function getEpisode($tvdbid, $season, $episode)
{
	global $sickbeard, $sb_api, $log, $lang, $error;
	
	$tag = 'getEpisode';
	$get_episode = getUrl($sickbeard . "/api/" . $sb_api . "/?cmd=episode&tvdbid=" . $tvdbid . "&season=" . $season . "&episode=" . $episode . "&full_path=1", $tag);
	if (!$get_episode)
	{
		$error[] = sprintf($lang['SB_NO_EPISODE'], $tvdbid);
		$log->error($tag, sprintf($lang['SB_NO_EPISODE'], $tvdbid));
		return;
	}
	$result = json_decode($get_episode, true);
	
	$failures = array('failure', 'error', 'fatal', 'Skipped', 'Unknown', 'Snatched', 'Wanted', 'Unaired', 'Archived', 'Ignored', '');  
	// Remove empty results
	if (in_array($result['result'], $failures))
	{
		return;
	}
	if (in_array($result['data']['status'], $failures))
	{
		return;
	}

	return $result;
}

function readXml($xml_file)
{
	global $log, $lang;
	
	$xml = simplexml_load_file($xml_file);
	$log->info('readXml', sprintf($lang['OPEN_XML'], $xml_file));
	$json = json_encode($xml);
	$result = json_decode($json, true);
	$result = array_change_key_case($result, CASE_LOWER);
	
	return $result;
}

function getBanner($tvdbid)
{
	$banner = $tvdbid . '.banner.jpg';
	unlink(CACHE_IMAGES . '/' . $banner);
	global $tvdb_token, $log, $error;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.thetvdb.com/series/$tvdbid");
	
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = "Authorization: Bearer $tvdb_token";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$array = json_decode($result, true);

	$url = 'https://artworks.thetvdb.com/banners/' . $array['data']['banner'];
	
	saveImage($url, $banner, $result['series']['SeriesName']);
}

function checkSub($array, $tvdbid)
{
	global $sub_ext, $lang, $log, $ignore_words, $skip_shows;
	
	$key = $tvdbid;
	if ($array[$key]['location'] == '')
	{
		return false;
	}
	// Check if there are subs downloaded for this episode
	$path_parts = pathinfo($array[$key]['location']);

	$find_sub = $path_parts['dirname'] . '/' . $path_parts['filename'] . $sub_ext;

	if (file_exists($find_sub))
	{
		$log->debug('checkSub', sprintf($lang['SUBTITLE_FOUND'], $find_sub));
		$log->info('checkSub', sprintf($lang['SUBTITLE_FOUND'], $array[$key]['show_name'] . ' ' . $array[$key]['episode']));
		$array[$key]['subbed'] = true;
	}
	else
	{
		$ignore_words_array = explode(",", strtolower($ignore_words));
		$skip_shows_array = array_map('trim', explode(",", strtolower($skip_shows)));

		if (!empty($ignore_words))
		{
			foreach ($ignore_words_array as $ignore_word)
			{
				if (strpos(strtolower($array[$key]['location']), $ignore_word) !== false)
				{
					$log->debug('checkSub', sprintf($lang['IGNORE_FOUND'], $ignore_word, $array[$key]['show_name'] . ' ' . $array[$key]['episode']));
					$array[$key]['subbed'] = true;
				}
			}
		}
		if (!empty($skip_shows))
		{
			foreach ($skip_shows_array as $skip_show)
			{
				if (strpos($array[$key]['tvdbid'], $skip_show) !== false)
				{
					$log->debug('checkSub', sprintf($lang['SKIP_FOUND'], $skip_show, $array[$key]['show_name'] . ' ' . $array[$key]['episode']));
					$array[$key]['subbed'] = true;
				}
			}
		}
	}
	if (!isset($array[$key]['subbed']))
	{
		$array[$key]['subbed'] = false;
	}
	
	return $array[$key]['subbed'];
}

function update_show($array)
{
	global $log, $cache, $lang;
	
	$log->debug('trakt.tv', $lang['TRAKT_UPDATE']);
	$key = key($array);
	$update_show = getShow($key);
	$update_episode = getEpisode($array[$key]['tvdbid'], $array[$key]['season'], $array[$key]['episode']);
	// Put it all in a array
	$update_serie[$key]['tvdbid'] = $key;
	$update_serie[$key]['show_name'] = $update_show[$key]['show_name'];
	//$update_serie[$key]['tvrage_id'] = $update_show[$key]['tvrage_id'];
	$update_serie[$key]['show_slug'] = $update_show[$key]['show_slug'];
	$update_serie[$key]['trakt_id'] = $array[$key]['trakt_id'];
	$update_serie[$key]['message'] = $array[$key]['show_name'] . ' ' . $array[$key]['season'] . 'x' . sprintf('%02d', $array[$key]['episode']) . ' ' . $array[$key]['episode_name'];
	$update_serie[$key]['season'] = $array[$key]['season'];
	$update_serie[$key]['episode'] = $array[$key]['season'] . 'x' . sprintf('%02d', $array[$key]['episode']);
	$update_serie[$key]['episode_number'] = $array[$key]['episode'];
	$update_serie[$key]['name'] = $update_episode['data']['name'];
	$update_serie[$key]['description'] = $update_episode['data']['description'];
	$update_serie[$key]['status'] = $update_episode['data']['status'];
	$update_serie[$key]['location'] = $update_episode['data']['location'];
	
	// Check if there are subs downloaded for this episode
	$check_sub_update = checkSub($update_serie, $key);
	$update_serie[$key]['subbed'] = $check_sub_update;
		
	if (!$update_serie[$key]['subbed'])
	{
		$log->debug('checkSub', sprintf($lang['NO_SUBTITLE_FOUND'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
		$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
		if ($data = $cache->get('shows'))
		{
			$data = json_decode($data, true);
			unset($data[$key]);
			$cache->put('shows', json_encode($data));
		}
	}
	else
	{
		if ($data = $cache->get('shows'))
		{
			$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
			$data = json_decode($data, true);
			$update_data = array_replace($data, $update_serie);
			$cache->put('shows', json_encode($update_data));
		}
		//unset($getnext,$data);
	}
}

function tvdb_get_token()
{
	global $log, $error;
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://api.thetvdb.com/login');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"apikey\": \"FEE77D5126632344\"\n}");

	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Accept: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$result_token = json_decode($result, true);
	$data = file('config.php'); // reads an array of lines
	function refresh_config1($data)
	{
		global $result_token;
		
		if (stristr($data, '$tvdb_token'))
		{
			return "\$tvdb_token = '" . $result_token['token'] . "';\n";
		}
		return $data;
	}
	$data = array_map('refresh_config1', $data);
	file_put_contents('config.php', implode('', $data));
}

function tvdb_refresh_token()
{
	global $tvdb_token, $log, $error;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.thetvdb.com/refresh_token');
	
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = "Authorization: Bearer $tvdb_token";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$result_token = json_decode($result, true);
	$data = file('config.php'); // reads an array of lines
	function refresh_config2($data)
	{
		global $result_token;
		
		if (stristr($data, '$tvdb_token'))
		{
			return "\$tvdb_token = '" . $result_token['token'] . "';\n";
		}
		return $data;
	}
	$data = array_map('refresh_config2', $data);
	file_put_contents('config.php', implode('', $data));
}

function tvdb_get_episode_description($tvdbid, $season, $episode)
{
	global $tvdb_token, $log, $error;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.thetvdb.com/series/$tvdbid/episodes/query?airedSeason=$season&airedEpisode=$episode");
	
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = "Authorization: Bearer $tvdb_token";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$array = json_decode($result, true);
	return $array['data'][0]['overview'];
}

function tvdb_get_episode($id)
{
	global $tvdb_token, $log, $error;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.thetvdb.com/episodes/$id");
	
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = "Authorization: Bearer $tvdb_token";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	curl_close($ch);
	$array = json_decode($result, true);
	return $array;
}