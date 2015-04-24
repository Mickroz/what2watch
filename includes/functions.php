<?php
if (!defined('IN_W2W'))
{
	exit;
}

function getFanart($cat, $location, $name, $id, $banner, $background)
{
	global $log, $lang;
	$tag = 'getFanart';

	$cat_banner = ($cat == 'tv' ? 'tvbanner' : 'moviebanner');
	$cat_bg = ($cat == 'tv' ? 'showbackground' : 'moviebackground');
	$grabbed = false;
	$fanart = getUrl("http://webservice.fanart.tv/v3/$cat/$id?api_key=b28b14e9be662e027cfbc7c3dd600405", $tag);

	$result = json_decode($fanart, true);

	if(isset($result[$cat_banner]))
	{
		if (file_put_contents(CACHE_IMAGES . '/' . $banner, fopen($result[$cat_banner][0]['url'], 'r')))
		{
			$grabbed = true;
			$log->debug($tag, sprintf($lang['GRABBING_FANART'], $cat_banner . ' ' . $result[$cat_banner][0]['url']));
			$error[] = sprintf($lang['SAVED_FANART'], $cat_banner, $result['name']);
			$log->info($tag, sprintf($lang['SAVED_FANART'], $cat_banner, $result['name']));
		}
		else
		{
			$error[] = sprintf($lang['SAVED_FANART_FAILED'], $cat_banner, $result['name']);
			$log->error($tag, sprintf($lang['SAVED_FANART_FAILED'], $cat_banner, $result['name']));
		}
	}
	if (!isset($result[$cat_banner]) && isset($result[$cat_bg]))
	{
		if (file_put_contents(CACHE_IMAGES . '/' . $background, fopen($result[$cat_bg][0]['url'], 'r')))
		{
			$log->debug($tag, sprintf($lang['GRABBING_FANART'], $cat_bg . ' ' . $result[$cat_bg][0]['url']));
			$error[] = sprintf($lang['SAVED_FANART'], $cat_bg, $result['name']);
			$log->info($tag, sprintf($lang['SAVED_FANART'], $cat_bg, $result['name']));
		}
		else
		{
			$error[] = sprintf($lang['SAVED_FANART_FAILED'], $cat_bg, $result['name']);
			$log->error($tag, sprintf($lang['SAVED_FANART_FAILED'], $cat_bg, $result['name']));
		}
	}
	if (!isset($result[$cat_banner]) && file_exists(CACHE_IMAGES . '/' . $background))
	{
		$log->debug($tag, sprintf($lang['CREATED_FANART'], $cat_bg . ' ' . $location . '/' . $name . '/' . $background));
		$rsr_org = imagecreatefromjpeg(CACHE_IMAGES . '/' . $background);
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

function createImage($title, $banner, $rsr_org, $im, $got_bg)
{
	global $log, $lang;
	
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
	imagejpeg($im, CACHE_IMAGES . '/' . $banner);

	// Free up memory
	imagedestroy($im);
	if ($got_bg)
	{
		imagedestroy($rsr_org);
	}
	$error[] = sprintf($lang['CREATED_BANNER'], $title);
	$log->info($tag, sprintf($lang['CREATED_BANNER'], $title));
}

function saveImage($url, $banner, $name)
{
	global $log, $lang;
	
	$tag = 'saveImage';
	
	$dir_to_save = CACHE_IMAGES;
	if (!is_dir($dir_to_save))
	{
		$log->debug($tag, 'Cannot find ' . $dir_to_save);
		mkdir($dir_to_save);
	}
	if (!file_exists($dir_to_save . '/' . $banner))
	{
		//$get_banner = file_get_contents($url);
		$get_banner = imagecreatefromjpeg($url);
		$save_banner = imagescale($get_banner, 1000, 185,  IMG_BICUBIC_FIXED);
		// Save the image
		imagejpeg($save_banner, CACHE_IMAGES . '/' . $banner);

		// Free up memory
		imagedestroy($get_banner);
		imagedestroy($save_banner);
		//file_put_contents($dir_to_save . '/' . $banner, $save_banner);
		$log->info($tag, sprintf($lang['SAVED_BANNER'], $name . ' (' . $dir_to_save . '/' . $banner . ')'));
	}
	return;
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

function get_slug($id, $trakt_token)
{
	global $log, $lang;
	
		$type = 'tvdb';
		$return = 'show';
		
	$log->info('getSlug', sprintf($lang['GET_SLUG'], $id));
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-v2launch.trakt.tv/search?id_type=$type&id=$id");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"trakt-api-version: 2",
		"trakt-api-key: $trakt_token"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
	}
	curl_close($ch);
	
	$result = json_decode($response, true);
	if (!empty($result))
	{
		$key = array_search('show', $result);
		return $result[$key][$return]['ids']['slug'];
	}
	else
	{
		return false;
	}
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
		$ref_commit = "25eddb150d199f24b20feda1d24812cf611e67e5";
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
		'web_username'		=> $data['web_username'],
		'web_password'		=> $data['web_password'],
		'ignore_words'		=> $data['ignore_words'],
		'skip_shows'		=> $data['skip_shows'],
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
		'web_username'		=> $_POST['web_username'],
		'web_password'		=> $_POST['web_password'],
		'ignore_words'		=> $_POST['ignore_words'],
		'skip_shows'		=> $_POST['skip_shows'],
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
	global $lang, $template, $template_path;
	
	$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');
	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'SHOWS_ACTIVE'	=> ($mode == 'shows' ? ' class="active"' : ''),
		'MOVIES_ACTIVE'	=> ($mode == 'movies' ? ' class="active"' : ''),
		'LOG_ACTIVE'	=> ($mode == 'viewlog' ? ' class="active"' : ''),
		'CONFIG_ACTIVE'	=> (($mode == 'config') ? ' class=active' : ''),
		'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
		'TEMPLATE_PATH'	=> 'styles/' . $template_path,
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