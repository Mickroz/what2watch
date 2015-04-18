<?php
if (!defined('IN_W2W'))
{
	exit;
}

function trakt_show_checkin($trakt_id, $message)
{
	global $trakt_token;
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-v2launch.trakt.tv/checkin");
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
	curl_close($ch);
	return $response;
}

function getProgress($slug, $trakt_token)
{
	global $log;
	
	$tag = 'getProgress';
	$log->debug($tag, "trying to get progress for " . $slug);
	$log->info($tag, "Opening URL https://api.trakt.tv/shows/$slug/progress/watched");
	
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
	}
	curl_close($ch);
	
	return $response;
}

function getShow($tvdbid)
{
	$tag = 'getShow';
	global $sickbeard, $sb_api, $log;
	
	$show_id = getUrl($sickbeard . "/api/" . $sb_api . "/?cmd=show&tvdbid=" . $tvdbid, $tag);
	if (!$show_id)
	{
		$error[] = "SickBeard api returned nothing for" . $tvdbid;
		$log->error($tag, "SickBeard api returned nothing for" . $tvdbid);
	}
	$result_show = json_decode($show_id, true);
	$log->debug($tag, "SickBeard returned " . $result_show['data']['show_name']);
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
				$log->debug('getSeason', "no seasons found for " . $result_show['data']['show_name']);
			}
			continue;
		}
		$s++;
		$log->debug('getSeason', "found season $padded for " . $result_show['data']['show_name']);
		$show_name[$tvdbid]['show_name'] = $result_show['data']['show_name'];
		$show_name[$tvdbid]['show_slug'] = get_slug($tvdbid);
		$show_name[$tvdbid]['location'] = $result_show['data']['location'];
		//$show_name[$tvdbid]['tvrage_slug'] = slugify($result_show['data']['tvrage_name']);
	}
	return $show_name;
}

function getEpisode($tvdbid, $season, $episode)
{
	global $sickbeard, $sb_api, $log;
	
	$tag = 'getEpisode';
	$get_episode = getUrl($sickbeard . "/api/" . $sb_api . "/?cmd=episode&tvdbid=" . $tvdbid . "&season=" . $season . "&episode=" . $episode . "&full_path=1", $tag);
	if (!$get_episode)
	{
		$error[] = "SickBeard api returned no episode data for tvdbid: $tvdbid";
		$log->error($tag, "SickBeard api returned no episode data for tvdbid: $tvdbid");
		return;
	}
	$result = json_decode($get_episode, true);
		
	// Remove empty results
	if ($result['result'] == 'failure' || $result['result'] == 'error' || $result['result'] == 'fatal')
	{
		return;
	}
	return $result;
}

function readXml($xml_file)
{
	global $log;
	
	$xml = simplexml_load_file($xml_file);
	$log->info('readXml', 'Opening XML ' . $xml_file);
	$json = json_encode($xml);
	$result = json_decode($json, true);
	$result = array_change_key_case($result, CASE_LOWER);
	
	return $result;
}

function getBanner($tvdbid)
{
	$banner = $tvdbid . '.banner.jpg';
	unlink(CACHE_IMAGES . '/' . $banner);
	$result = readXml("http://thetvdb.com/api/FEE77D5126632344/series/$tvdbid/");
	$url = 'http://thetvdb.com/banners/' . $result['series']['banner'];
				
	saveImage($url, $banner, $result['series']['SeriesName']);
}