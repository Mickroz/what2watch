<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>What2Watch</title>
<style type="text/css">
.container {
	width: 500px;
	clear: both;
}
.container img {
	width: 500px;
}
.container input {
	width: 100%;
	clear: both;
}
input.button {
	width: auto;
	margin: 0 auto;
}
fieldset.submit-buttons {
	text-align: center;
	vertical-align: middle;
	margin: 5px 0;
	border-width: 0;
}
.header {
	letter-spacing: 1px;
	color: #fff;
	text-align: left;
	background-color: #333;
	font-weight: bold;
	font-size: 14px;
	font-family: Verdana, "Helvetica", sans-serif;
	padding: 4px;
}
.footer {
	color: #000;
	background: #f5fafa;
	border-right: 1px solid #d2ebe8;
	border-bottom: 1px solid #d2ebe8;
	border-left: 1px solid #d2ebe8;
	font-size: 14px;
	font-family: Verdana, "Helvetica", sans-serif;
	text-align: right;
	padding: 4px;
}
</style>
</head>
<body>' . "\n";
$config = $passed = false;
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
	echo '<h4>What 2 Watch | <a href="?cache=purge">Clear Cache!</a></h4>' . "\n";

	if (isset($_GET['cache']) && $_GET['cache'] == 'purge')
    {
        unlink($cache_file);
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
		if (!sizeof($error))
		{
			// Save array as json
			file_put_contents($cache_file, json_encode($eps));
			$cached = $eps;
			$passed = true;
		}
		else
		{
			echo '<strong style="color:red">' . implode('<br />', $error) . '</strong>' . "\n";
		}
	}
	else
	{
		// Retrieve json and decode to array
		$cached = json_decode(file_get_contents($cache_file), true);
		$passed = true;
	}
	if ($passed)
	{
		foreach ($cached as $a => $b)
		{
			// Lets grab the banner
			// TODO save banner on disk for later usage?
			$banner = $sickbeard . "/api/" . $sb_api . "/?cmd=show.getbanner&tvdbid=" . $a;
			echo '<div class="header">' . $b['show_name'] . '</div>' . "\n";
			echo '<div><img src="' . $banner . '" /></div>' . "\n";
			echo '<div class="footer">' . $b['episode'] . ' - ' . $b['name'] . '</div>' . "\n";
			echo '<br />' . "\n";
		}
	}
	echo '</div>' . "\n";
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