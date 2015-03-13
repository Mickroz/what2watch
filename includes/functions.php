<?php
if (!defined('IN_W2W'))
{
	exit;
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

function createXml($filename)
{
	$tag = 'createXml';
	global $log;
	
	$beforeBracket = current(explode('[', $filename));
	$beforeBracket = str_replace('.xml', '', $beforeBracket);
	$new_string = preg_replace("/(19|20)\d{2}/", '', $beforeBracket);
	$new_string = slugify($new_string);
	$json = "http://www.omdbapi.com/?t=$new_string&y=&plot=short&r=json";
	$log->info($tag, 'Opening URL ' . $json);
	$jsonstring = file_get_contents($json);
	$jsonarray = json_decode($jsonstring, true);
	if ($jsonarray['Response'] == 'False')
	{
		$error[] = 'Movie not found from OMDBAPI for ' . $new_string;
		$log->error($tag, 'Movie not found on OMDBAPI for ' . $new_string);
	}
	else
	{
		$jsonarray = array_change_key_case($jsonarray, CASE_LOWER);
		$jsonarray = array_flip($jsonarray);
		$xml = new SimpleXMLElement('<movie/>');
		array_walk_recursive($jsonarray, array ($xml, 'addChild'));
						
		if (file_put_contents($movies_folder . '/' . $value . '/' . $filename, $xml->asXML()))
		{
			$error[] = 'Saved xml file from OMDBAPI for ' . $new_string;
			$log->info($tag, 'Saved xml file from OMDBAPI for ' . $new_string);
		}
		else
		{
			$error[] = 'Failed saving xml file from OMDBAPI for ' . $new_string;
			$log->error($tag, 'Failed saving xml file from OMDBAPI for ' . $new_string);
		}
	}
}

function getFanart($cat, $location, $name, $id, $banner, $background)
{
	global $log;
	$tag = 'getFanart';

	$cat_banner = ($cat == 'tv' ? 'tvbanner' : 'moviebanner');
	$cat_bg = ($cat == 'tv' ? 'showbackground' : 'moviebackground');
	$grabbed = false;
	$fanart = getUrl("http://webservice.fanart.tv/v3/$cat/$id?api_key=b28b14e9be662e027cfbc7c3dd600405", $tag);

	$result = json_decode($fanart, true);

	if(isset($result[$cat_banner]))
	{
		if (file_put_contents($location . '/' . $name . '/' . $banner, fopen($result[$cat_banner][0]['url'], 'r')))
		{
			$grabbed = true;
			$log->debug($tag, 'grabbing ' . $cat_banner . ' ' . $result[$cat_banner][0]['url']);
			$error[] = 'Saved ' . $cat_banner . ' from fanart.tv for ' . $result['name'];
			$log->info($tag, 'Saved ' . $cat_banner . ' from fanart.tv for ' . $result['name']);
		}
		else
		{
			$error[] = 'Failed saving ' . $cat_banner . ' from fanart.tv for ' . $result['name'];
			$log->error($tag, 'Failed saving ' . $cat_banner . ' from fanart.tv for ' . $result['name']);
		}
	}
	if (!isset($result[$cat_banner]) && isset($result[$cat_bg]))
	{
		if (file_put_contents($location . '/' . $name . '/' . $background, fopen($result[$cat_bg][0]['url'], 'r')))
		{
			$log->debug($tag, 'grabbing ' . $cat_bg . ' ' . $result[$cat_bg][0]['url']);
			$error[] = 'Saved ' . $cat_bg . ' from fanart.tv for ' . $result['name'];
			$log->info($tag, 'Saved ' . $cat_bg . ' from fanart.tv for ' . $result['name']);
		}
		else
		{
			$error[] = 'Failed saving ' . $cat_bg . ' from fanart.tv for ' . $result['name'];
			$log->error($tag, 'Failed saving ' . $cat_bg . ' from fanart.tv for ' . $result['name']);
		}
	}
	if (!isset($result[$cat_banner]) && file_exists($location . '/' . $name . '/' . $background))
	{
		$log->debug($tag, 'creating image from ' . $cat_bg . ' ' . $location . '/' . $name . '/' . $background);
		$rsr_org = imagecreatefromjpeg($location . '/' . $name . '/' . $background);
		$im = imagescale($rsr_org, 1000, 185,  IMG_BICUBIC_FIXED);
		$got_bg = true;
	}
	else
	{
		// Create the image
		$rsr_org = '';
		$im = imagecreatetruecolor(1000, 185);
		$got_bg = false;
	}
	$array = array(
		'rsr_org'	=> $rsr_org,
		'im'		=> $im,
		'got_bg'	=> $got_bg,
		'grabbed'	=> $grabbed,
	);
	return $array;
}

function createImage($location, $name, $title, $banner, $rsr_org, $im, $got_bg)
{
	global $log;
	
	$tag = 'createImage';
	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	//imagefilledrectangle($im, 0, 0, 399, 29, $white);

	// The text to draw
	$text = $title;
	// Replace path by your own font path
	$font = 'movie.ttf';

	// Add some shadow to the text
	imagettftext($im, 72, 0, 19, 129, $grey, $font, $text);

	// Add the text
	imagettftext($im, 72, 0, 20, 128, $text_color, $font, $text);

	// Save the image
	imagejpeg($im, $location . '/' . $name . '/' . $banner);

	// Free up memory
	imagedestroy($im);
	if ($got_bg)
	{
		imagedestroy($rsr_org);
	}
	$error[] = 'Created banner for ' . $title;
	$log->info($tag, 'Created banner for ' . $title);
}

function saveImage($url, $banner, $name)
{
	global $log;
	
	$tag = 'saveImage';
	
	$dir_to_save = $_SERVER['DOCUMENT_ROOT'] . '/what2watch/images/';
	if (!is_dir($dir_to_save))
	{
		$log->debug($tag, 'Cannot find ' . $dir_to_save);
		mkdir($dir_to_save);
	}
	if (!file_exists($dir_to_save . $banner))
	{
		$get_banner = file_get_contents($url);
		file_put_contents($dir_to_save . $banner, $get_banner);
		$log->info($tag, 'Saved banner for ' . $name . ' (' . $dir_to_save . $banner . ')');
	}
	return;
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
		$show_name[$tvdbid]['show_slug'] = slugify($result_show['data']['show_name']);
		$show_name[$tvdbid]['location'] = $result_show['data']['location'];
		//$show_name[$tvdbid]['tvrage_slug'] = slugify($result_show['data']['tvrage_name']);
	}
	return $show_name;
}
function getUrl($url, $tag='getUrl')
{
	global $log;

	$log->info($tag, "Opening URL " . $url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'What2Watch');
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

function slugify($phrase)
{
    $result = strtolower($phrase);
	$result = str_replace("'", "-", $result);
	$result = str_replace(".", "-", $result);
    $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
    $result = trim(preg_replace("/[\s-]+/", " ", $result));
    $result = preg_replace("/\s/", "-", $result);
    
    return $result;
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

/*
* This code was created by Philippe MC
* https://github.com/xaccrocheur/
*/
function version_check()
{
	global $lang;
	$tag = 'versionCheck';
	$current_commits = getUrl("https://api.github.com/repos/Mickroz/what2watch/commits", $tag);

	if ($current_commits !== false)
	{
		$commits = json_decode($current_commits);
		$ref_commit = "c8e6d5a81644a69a548fa36e3a4e0f78925cae6d";
		$current_commit_minus1 = $commits[1]->sha;
		$commit_message = $commits[0]->commit->message;
		
		if (!strcmp($current_commit_minus1, $ref_commit)) {
			$version['style'] = ' style="color: #228822;"';
			$version['message'] = $lang['VERSION_UP_TO_DATE'];
		}
		else
		{
			$version['style'] = ' style="color: #BC2A4D;"';
			$version['message'] = sprintf($lang['VERSION_NOT_UP_TO_DATE'], $commit_message);
		}
	}
	else
	{
		$version['style'] = '';
		$version['message'] = $lang['VERSIONCHECK_FAIL'];
	}
	
	return $version;
}
function install()
{
	//install code
}

function update()
{
	//update code
}

function create_config_file_data($data)
{
	$config_data = "<?php\n";
	$config_data .= "// sickbeard should be with http:// and port\n";
	$config_data .= "// cache_life is caching time, in seconds\n";
	$config_data_array = array(
		'trakt_token'		=> $data['trakt_token'],
		'trakt_expires_in'	=> $data['trakt_expires_in'],
		'sickbeard'			=> $data['sickbeard'],
		'sb_api'			=> $data['sb_api'],
		'cache_life' 		=> $data['cache_life'],
		'sub_ext'			=> $data['sub_ext'],
		'movies_folder'		=> $data['movies_folder'],
		'template_path'		=> $data['template_path'],
		'language'			=> $data['language'],
		'config_version'	=> $data['config_version'],
	);

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}
	
	return $config_data;
}

function create_config_file()
{
	global $lang, $template;
	$data = get_submitted_data();
	$written = false;
	$config_options = $data;
	$config_data = create_config_file_data($data);
	
	// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
	if (file_exists('config.php') && is_writable('config.php'))
	{
		// Assume it will work ... if nothing goes wrong below
		$written = true;

		if (!($fp = @fopen('config.php', 'w')))
		{
			// Something went wrong ...
			$written = false;
		}
		if (!(@fwrite($fp, $config_data)))
		{
			// Something went wrong ... 
			$written = false;
		}
		@fclose($fp);
		
		if ($written)
		{
			$chmod = @chmod('config.php', 0644);
			if (!$chmod)
			{
				$error[] = $lang['FAILED_CHMOD'];
			}
		}
	}
	if (isset($_POST['dldone']))
	{
		// Do a basic check to make sure that the file has been uploaded
		// Note that all we check is that the file has _something_ in it
		// We don't compare the contents exactly - if they can't upload
		// a single file correctly, it's likely they will have other problems....
		if (filesize('config.php') > 10)
		{
			$written = true;
		}
	}
	
	if (!$written)
	{
		$s_hidden_fields = '';
		foreach ($data as $config_key => $config_value)
		{
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $config_value . '" />';
		}
		$first_run = false;
		// OK, so it didn't work let's try the alternatives

		if (isset($_POST['dlconfig']))
		{
			// They want a copy of the file to download, so send the relevant headers and dump out the data
			header("Content-Type: text/x-delimtext; name=\"config.php\"");
			header("Content-disposition: attachment; filename=config.php");
			echo $config_data;
			exit;
		}

		// The option to download the config file is always available, so output it here
		page_header($lang['INDEX'] . ' - ' . $lang['DL_CONFIG']);
		$template->set_filename('install_dlconfig.html');
		$template->assign_vars(array(
			'STYLESHEET_LINK'	=> 'styles/default/style.css',
			'VERSION'	=> '',
			'S_HIDDEN'				=> $s_hidden_fields,
		));
		page_footer();
	}
	else
	{
		page_header($lang['INDEX'] . ' - ' . $lang['CONFIG_WRITTEN']);
		$template->set_filename('index_body.html');
		$template->assign_vars(array(
			'ERROR'		=> (sizeof($error)) ? '<p class="error">' . implode('<br />', $error) . '</p>' : '',
			'CONTENT'	=> $lang['CONFIG_WRITTEN_EXPLAIN'],
		));

		page_footer();
	}
}
/**
* Get submitted data
*/
function get_submitted_data()
{
	return array(
		'trakt_token'		=> $_POST['trakt_token'],
		'trakt_expires_in'	=> $_POST['trakt_expires_in'],
		'sickbeard'			=> $_POST['sickbeard'],
		'sb_api'			=> $_POST['sb_api'],
		'cache_life'		=> $_POST['cache_life'],
		'sub_ext'			=> $_POST['sub_ext'],
		'movies_folder'		=> $_POST['movies_folder'],
		'language'			=> $_POST['language'],
		'template_path'		=> $_POST['template_path'],
		'config_version'	=> $_POST['config_version'],
	);
}

function set_lang($language)
{
	global $lang;
	$language_filename = 'language/' . $language . '/lang.php';
	
	if (!file_exists($language_filename))
	{
		if ($language == 'en')
		{
			// The user's selected language is missing and the default language is missing
			$language_filename = str_replace('language/en', 'language/' . $language, $language_filename);
			trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
		}
		else
		{
			// Fall back to the English Language
			$this->set_lang('en');
		}
		return;
	}
	$include_result = include $language_filename;
	if ($include_result === false)
	{
		trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
	}
}
/**
* Generate page header
*/
function page_header($page_title = '')
{
	global $lang, $template;
	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'PAGE_TITLE'	=> $page_title,
	));
}

/**
* Generate page footer
*/
function page_footer()
{
	global $lang, $template;
	
	echo $template->output();
}