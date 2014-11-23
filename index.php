<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-15">
<meta name="viewport" content="width=device-width">
<title>What2Watch</title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen, handheld, projection">
</head>
<body>' . "\n";
$output = '';
$skip = $config = $passed = false;
$cached = $error = array();
if (file_exists('config.php'))
{
	include('config.php');
	$config = true;
}
// filling the config file with the filled in values
if (isset($_POST['submit']))
{
	$config_data = "<?php\n";
	$config_data .= "// sickbeard should be with http:// and port\n";
	$config_data .= "// cache_life is caching time, in seconds\n";
	$config_data_array = array(
		'trakt_api'			=> $_POST['trakt_api'],
		'trakt_username'	=> $_POST['trakt_username'],
		'sickbeard'			=> $_POST['sickbeard'],
		'sb_api'			=> $_POST['sb_api'],
		'cache_file' 		=> 'cached.json',
		'cache_life' 		=> '3600',
		'sub_ext'			=> '.nl.srt',
	);

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}
	$config_data .= "?>\n";

	if (!($fp = @fopen('config.php', 'w')))
	{
		// Something went wrong ...
		$error[] = "Cannot open config file";
	}
	if (!(@fwrite($fp, $config_data)))
	{
		// Something went wrong ... 
		$error[] = "Cannot write to config file";
	}
	@fclose($fp);

	include('config.php');
	$config = true;
}

if ($config)
{
	echo '<div class="container">' . "\n";
	if (isset($_GET['cache']) && $_GET['cache'] == 'purge')
    {
		if (!isset($_GET['skip']))
		{
			unlink($cache_file);
		}
		header("refresh:5; url=index.php"); 
		echo 'Cache cleared, You\'ll be redirected in about 5 secs. If not, click <a href="index.php">here</a>.';
		exit;
    }
	$filemtime = @filemtime($cache_file);  // returns FALSE if file does not exist
	if (!$filemtime or (time() - $filemtime >= $cache_life))
	{
		// We have a config, lets get started with grabbing all shows from sickbeard
		$response = curl($sickbeard . "/api/" . $sb_api . "/?cmd=shows&sort=name");
		if (!$response)
		{
			$error[] = "SickBeard api returned no shows";
			$skip = true;
			goto skip;
		}
		$result = json_decode($response, true);

		foreach ($result['data'] as $show => $values)
		{	
			$show_id = curl($sickbeard . "/api/" . $sb_api . "/?cmd=show&tvdbid=" . $values['tvdbid']);
			if (!$show_id)
			{
				$error[] = "SickBeard api returned nothing for" . $values['tvdbid'];
			}
			$result_show = json_decode($show_id, true);
			// Checking which show actually has a episode downloaded
			// and put all  tvdb id's in an array
			// TODO grab naming pattern
			$season_list = $result_show['data']['season_list'];
			$shows[$values['tvdbid']]['show_name'] = $result_show['data']['show_name'];
			foreach ($season_list as $id => $season)
			{
				$padded = sprintf('%02d', $season); 
				$dir = $result_show['data']['location'] . "/Season " .  $padded;
				if (!is_dir($dir))
				{
					continue;
				}
				$series[] = $values['tvdbid'];
			}
		}

		$result_series = array_unique($series);
		// Create a string for trakt and get user Progress
		$comma_separated = implode(",", $result_series);
		$buffer = curl("http://api.trakt.tv/user/progress/watched.json/" . $trakt_api . "/" . $trakt_username . "/" . $comma_separated);
		if (!$buffer)
		{
			$error[] = "Trakt api returned no progress";
			$skip = true;
			goto skip;
		}
		else
		{
			$remove_pilots = $result_trakt = json_decode($buffer, true);
			foreach ($remove_pilots as $x => $y)
			{
				$title = $y['next_episode']['title'];
				// Check if the episode title contains Pilot
				// If true we get the first episode data from sickbeard
				// Trakt api search for Pilot takes too long
				if (strpos($title, 'Pilot') !== false)
				{
					// We remove the result from $result_trakt
					unset($result_trakt[$x]);
				}
				if (!$y['next_episode'])
				{
					// We remove the result from $result_trakt
					unset($result_trakt[$x]);
					if(($next = array_search($y['show']['tvdb_id'], $result_series)) !== false)
					{
						unset($result_series[$next]);
					}
				}
			}
			foreach ($result_trakt as $key => $value)
			{
				$tvdbid = $value['show']['tvdb_id'];
				$title = $value['next_episode']['title'];
				// If the tvdbdid == 0 we search for the episode name on trakt and compare that tvdbid result against our series array
				if ($tvdbid == '0')
				{
					$search = curl('http://api.trakt.tv/search/episodes.json/' . $trakt_api . '?query="' . urlencode($title) . '"');
					if (!$search)
					{
						$error[] = "Trakt search returned nothing";
					}
					$result_search = json_decode($search, true);
					foreach ($result_search as $k => $v)
					{
						$find = $v['show']['tvdb_id'];
				
						if (in_array($find, $result_series))
						{
							$tvdbid = $find;
						}
					}
				}
				// We have to remove the tvdbid's from $result_series to get the remaining tvdbid's for the Pilot episodes
				if(($remove = array_search($tvdbid, $result_series)) !== false)
				{
					unset($result_series[$remove]);
				}
				// Grab all episode data
				$episode = curl($sickbeard . "/api/" . $sb_api . "/?cmd=episode&tvdbid=" . $tvdbid . "&season=" . $value['next_episode']['season'] . "&episode=" . $value['next_episode']['number'] . "&full_path=1");
				if (!$episode)
				{
					$error[] = "SickBeard api returned no episode data";
				}
				$result_eps = json_decode($episode, true);
				// Remove empty results
				if ($result_eps['result'] == 'failure')
				{
					continue;
				}
				// Put it all in a array
				$eps[$tvdbid]['show_name'] = $shows[$tvdbid]['show_name'];
				$eps[$tvdbid]['episode'] = $value['next_episode']['season'] . 'x' . sprintf('%02d', $value['next_episode']['number']);
				$eps[$tvdbid]['name'] = $result_eps['data']['name'];
				$eps[$tvdbid]['description'] = $result_eps['data']['description'];
				$eps[$tvdbid]['status'] = $result_eps['data']['status'];
				$eps[$tvdbid]['location'] = $result_eps['data']['location'];
			
				// Check if there are subs downloaded for this episode
				$search = array('.mkv', '.avi', '.mpeg');
				$find_sub = str_replace($search, $sub_ext, $result_eps['data']['location']);
				if (file_exists($find_sub))
				{
					$eps[$tvdbid]['sub'] = true;
				}
				else
				{
					unset($eps[$tvdbid]);
				}
			}
		}
		foreach ($result_series as $c => $d)
		{
			$pilot = curl($sickbeard . "/api/" . $sb_api . "/?cmd=episode&tvdbid=" . $d . "&season=1&episode=1&full_path=1");
			if (!$pilot)
			{
				$error[] = "SickBeard api returned no Pilot episode data";
			}
			$result_pilot = json_decode($pilot, true);
		
			// Put it all in a array
			$eps[$d]['show_name'] = $shows[$d]['show_name'];
			$eps[$d]['episode'] = '1x01';
			$eps[$d]['name'] = $result_pilot['data']['name'];
			$eps[$d]['description'] = $result_pilot['data']['description'];
			$eps[$d]['status'] = $result_pilot['data']['status'];
			$eps[$d]['location'] = $result_pilot['data']['location'];
			
			// Check if there are subs downloaded for this episode
			$search = array('.mkv', '.avi', '.mpeg');
			$find_sub = str_replace($search, $sub_ext, $result_pilot['data']['location']);
			if (file_exists($find_sub))
			{
				$eps[$d]['sub'] = true;
			}
			else
			{
				unset($eps[$d]);
			}
		}
		// Save array as json
		if (file_put_contents($cache_file, json_encode($eps)))
		{
			$cached = $eps;
			$passed = true;
		}
		else
		{
			$error[] = "Could not create $cache_file";
		}
		skip:
		// We have errors
		if (sizeof($error))
		{
			// We unset cached here, because of errors, we also delete the $cache_file because it will not be complete
			$output .= '<strong style="color:red">' . implode('<br />', $error) . '</strong>' . "\n";
			unset($cached);
			$passed = false;
			if ($skip === false)
			{
				unlink($cache_file);
			}
		}
	}
	else
	{
		// Retrieve json and decode to array
		// we use clearstatcache() here because the result of file_exists() is cached
		clearstatcache();
		if (file_exists($cache_file))
		{
			if ('' == file_get_contents($cache_file))
			{
				$error[] = "$cache_file is empty";
			}
		}
		else
		{
			$error[] = "Couldn't find $cache_file";
		}
		if (!sizeof($error))
		{
			$cached = json_decode(file_get_contents($cache_file), true);
			$passed = true;
		}
		else
		{
			$output .= '<strong style="color:red">' . implode('<br />', $error) . '</strong>' . "\n";
		}
	}
	echo '<h4>What 2 Watch | <a href="?cache=purge' . ((!empty($skip)) ? '&skip=1' : '') . '">Clear Cache!</a></h4>' . "\n";
	echo $output;
	if ($passed)
	{
		foreach ($cached as $a => $b)
		{
			// Lets grab the banner
			// First check if the folder exists, if not create it.
			$dir_to_save = __DIR__ . '/images/';
			if (!is_dir($dir_to_save))
			{
				mkdir($dir_to_save);
			}
			if (!file_exists($dir_to_save . $a . '.banner.jpg'))
			{
				$banner = file_get_contents($sickbeard . "/api/" . $sb_api . "/?cmd=show.getbanner&tvdbid=" . $a);
				file_put_contents($dir_to_save . $a . '.banner.jpg', $banner);
			}
			echo '<div class="header">' . $b['show_name'] . '</div>' . "\n";
			echo '<div><a id="displayText' . $a . '" href="javascript:toggle' . $a . '();"><img src="images/' . $a . '.banner.jpg' . '" /></a></div>' . "\n";
			echo '<div id="toggleText' . $a . '" class="description" style="display: none">' . $b['description'] . '</div>' . "\n";
			echo '<div class="footer">' . $b['episode'] . ' - ' . $b['name'] . '</div>' . "\n";
			echo '<br />' . "\n";
			echo '<script type="text/javascript" > ' . "\n";
			echo 'function toggle' . $a . '() {' . "\n";
			echo '	var ele = document.getElementById("toggleText' . $a . '");' . "\n";
			echo '	var text = document.getElementById("displayText' . $a . '");' . "\n";
			echo '	if(ele.style.display == "block") {' . "\n";
			echo '    		ele.style.display = "none";' . "\n";
			echo '  	}' . "\n";
			echo '	else {' . "\n";
			echo '		ele.style.display = "block";' . "\n";
			echo '	}' . "\n";
			echo '} ' . "\n";
			echo '</script>' . "\n";
		}
	}
	echo $_SERVER['DOCUMENT_ROOT'] . '</div>' . "\n";
}
else
{
	// First run, let's create a config file
	echo '<div class="container">';
	echo '<form action="' . $_SERVER['SCRIPT_NAME'] . '" method="post">';
	echo '<h1>Setup</h1>';
	echo '<label>Trakt.tv API key:</label> <input name="trakt_api" type="text" /><br />';
	echo '<label>Trakt.tv Username:</label> <input name="trakt_username" type="text" /><br />';
	echo '<label>SickBeard url:</label> <input name="sickbeard" type="text" placeholder="http://localhost:8081" /><br />';
	echo '<label>SickBeard API key:</label> <input name="sb_api" type="text" /><br />';
	echo '<fieldset class="submit-buttons"><input type="submit" name="submit" value="Create config.php" class="button" /></fieldset>';
	echo '</form></div>';
}
echo '</body>
</html>';
function curl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ($httpcode>=200 && $httpcode<300) ? $data : false;
}
?>