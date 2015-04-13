<?php
if (!defined('IN_W2W'))
{
	exit;
}

function trakt_movie_checkin()
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-v2launch.trakt.tv/checkin");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_POST, TRUE);

	curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		\"movie\": {
		\"title\": \"Guardians of the Galaxy\",
		\"year\": 2014,
		\"ids\": {
			\"trakt\": 28,
			\"slug\": \"guardians-of-the-galaxy-2014\",
			\"imdb\": \"tt2015381\",
			\"tmdb\": 118340
		}
	},
		\"sharing\": {
			\"facebook\": true,
			\"twitter\": true,
			\"tumblr\": false
		},
		\"message\": \"Guardians of the Galaxy FTW!\",
		\"app_version\": \"1.0\",
		\"app_date\": \"2014-09-22\"
	}");

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer [token]",
		"trakt-api-version: 2",
		"trakt-api-key: [client_id]"
	));

	$response = curl_exec($ch);
	curl_close($ch);
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
	global $log;
	
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
			$error[] = 'Movie not found from OMDBAPI for ' . $new_string;
			$log->error('getImdbTitle', 'Movie not found on OMDBAPI for ' . $new_string);
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
		$error[] = 'Saved xml file from ' . $output . ' for ' . $result['title'];
		$log->info($tag, 'Saved xml file from ' . $output . ' for ' . $result['title']);
	}
	else
	{
		$error[] = 'Failed saving xml file from ' . $output . ' for ' . $result['title'];
		$log->error($tag, 'Failed saving xml file from ' . $output . ' for ' . $result['title']);
	}
}