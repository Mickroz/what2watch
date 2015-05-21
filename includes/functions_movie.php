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

function trakt_movie_checkin($imdb_id, $message)
{
	global $trakt_token;
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-v2launch.trakt.tv/checkin");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_POST, TRUE);

	curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		\"movie\": {
		\"ids\": {
			\"imdb\": \"$imdb_id\"
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

function getMovieMeter($imdbid)
{
	$moviemeter = getUrl("http://www.moviemeter.nl/api/film/$imdbid?api_key=667gremmf36pxpkegnj45df8fkube7ab", 'getMovieMeter');
	$mmresult = json_decode($moviemeter, true);
	
	return $mmresult;
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

function createXml($filename, $location = false)
{
	$tag = 'createXml';
	global $log, $lang;
	
	$got_file = false;
	$cache_dir = CACHE_XML;
	if (!is_dir($cache_dir))
	{
		$log->debug($tag, 'Cannot find ' . $cache_dir);
		mkdir($cache_dir);
	}

	if ($location)
	{
		$result = readXml($location . '/' . $filename);
	}
	else
	{
		$beforeBracket = current(explode('[', $filename));
		$beforeBracket = str_replace('.xml', '', $beforeBracket);
		$new_string = preg_replace("/(19|20)\d{2}/", '', $beforeBracket);
		$new_string = slugify($new_string);
		$omdbapi = getUrl("http://www.omdbapi.com/?t=$new_string&y=&plot=short&r=json", 'getImdbTitle');
		$result = json_decode($omdbapi, true);
		$result = array_change_key_case($result, CASE_LOWER);
		
		if ($result['response'] == 'False')
		{
			$error[] = sprintf($lang['OMDB_MOVIE_FAILED'], $new_string);
			$log->error('getImdbTitle', sprintf($lang['OMDB_MOVIE_FAILED'], $new_string));
		}
	}
	$imdbid = (isset($result['id'])) ? $result['id'] : $result['imdbid'];
	$moviemeter = getMovieMeter($imdbid);
	$final = array(
		'movieid' 	=> $imdbid,
		'title' 	=> isset($result['title']) ? $result['title'] : $result['originaltitle'],
		'runtime' 	=> isset($result['runtime']) ? $result['runtime'] : $moviemeter['runtime'],
		'year' 		=> isset($result['year']) ? $result['year'] : $moviemeter['year'],
		'mpaa'		=> isset($result['mpaa']) ? $result['mpaa'] : 'Not Rated',
		'plot' 		=> isset($moviemeter['plot']) ? $moviemeter['plot'] : $result['plot'],
		'genre' 	=> is_array($result['genre']) ? implode(",", $result['genre']) : $result['genre'],
	);

	$final = array_flip($final);
	
	$xml = new SimpleXMLElement('<movie/>');
	array_walk_recursive($final, array ($xml, 'addChild'));
	
	$output = !empty($location) ? $location : 'OMDBAPI';
	if (file_put_contents($cache_dir . '/' . $filename, $xml->asXML()))
	{
		$error[] = sprintf($lang['SAVED_XML'], $output, $result['title']);
		$log->info($tag, sprintf($lang['SAVED_XML'], $output, $result['title']));
	}
	else
	{
		$error[] = sprintf($lang['FAILED_XML'], $output, $result['title']);
		$log->error($tag, sprintf($lang['FAILED_XML'], $output, $result['title']));
	}
}