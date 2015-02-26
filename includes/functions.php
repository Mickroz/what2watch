<?php
if (!defined('IN_W2W'))
{
	exit;
}

function curl($url)
{
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
function get_progress($slug, $trakt_token)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/shows/$slug/progress/watched");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

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

/*
* This code was created by Philippe MC
* https://github.com/xaccrocheur/
*/
function version_check()
{
	global $lang;
	$current_commits = curl("https://api.github.com/repos/Mickroz/what2watch/commits");

	if ($current_commits !== false)
	{
		$commits = json_decode($current_commits);
		$ref_commit = "fda07d3dcb7d727bea790db3675fb3529ad46dc6";
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
		'template_path'		=> $_POST['template_path'],
		'language'			=> $_POST['language'],
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