<?php
if (!defined('IN_W2W'))
{
	exit;
}

$submit	= (isset($_POST['submit'])) ? true : false;
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$post_data = $lang_pack = $error = array();
$trakt_token = $trakt_expires_in = $sickbeard = $sb_api = $cache_life = $sub_ext = $movies_folder = $language = $config_version = '';
$install_version = '1.0.0';
$first_run = true;

if (file_exists('config.php'))
{
	require('config.php');
}

if (!$config_version || $config_version != $install_version)
{
	// Redirect the user to the installer
	require('includes/functions.php');
	require('includes/template.php');

	$template_path = 'default';
	set_lang('en');
	$version = version_check();
	if($mode == 'config_file')
	{
		create_config_file();
		$first_run = false;
	}

	if(isset($_GET['access_token']))
	{
		$first_run = false;
		$post_data['sickbeard'] = (isset($_POST['sickbeard']) ? $_POST['sickbeard'] : $sickbeard);
		$post_data['sb_api'] = (isset($_POST['sb_api']) ? $_POST['sb_api'] : $sb_api);
		$post_data['trakt_token'] = (isset($_POST['trakt_token']) ? $_POST['trakt_token'] : $_GET['access_token']);
		$post_data['trakt_expires_in'] = (isset($_POST['trakt_expires_in']) ? $_POST['trakt_expires_in'] : $_GET['expires_in']);
		$post_data['cache_life'] = (isset($_POST['cache_life']) ? $_POST['cache_life'] : $cache_life);
		$post_data['sub_ext'] = (isset($_POST['sub_ext']) ? $_POST['sub_ext'] : $sub_ext);
		$post_data['movies_folder'] = (isset($_POST['movies_folder']) ? $_POST['movies_folder'] : $movies_folder);
		$post_data['language'] = (isset($_POST['language']) ? $_POST['language'] : $language);
		$post_data['template_path'] = (isset($_POST['template_path']) ? $_POST['template_path'] : $template_path);
		$post_data['config_version'] = $install_version;
		
		$directory = 'language/';
		$scanned_directory = array_diff(scandir($directory), array('..', '.'));

		foreach ($scanned_directory as $key => $value) 
		{
			$lang_iso = $value;
			if (!file_exists("language/$lang_iso/iso.txt"))
			{
				trigger_error(sprintf($lang['MISSING_LANG_FILES'], $lang_iso), E_USER_WARNING);
			}

			$file = file("language/$lang_iso/iso.txt");

			$lang_pack[$lang_iso] = array(
				'iso'		=> $lang_iso,
				'name'		=> trim(htmlspecialchars($file[0])),
				'local_name'=> trim(htmlspecialchars($file[1], ENT_COMPAT, 'UTF-8'))
			);
			unset($file);
		}
		$s_lang_options = '';
		foreach ($lang_pack as $iso => $value)
		{
			$selected = ($iso == $post_data['language']) ? ' selected="selected"' : '';
			$s_lang_options .= '<option value="' . $iso . '"' . $selected . '>' . $value['name'] . '</option>';
		}
		
		$install = new template();
		$install->set_template();
		$install->set_filename('install_body.html');
		
		$install->assign_vars(array(
			'SICKBEARD'			=> $post_data['sickbeard'],
			'SB_API'			=> $post_data['sb_api'],
			'TRAKT_TOKEN'		=> $post_data['trakt_token'],
			'TRAKT_EXPIRES_IN'	=> $post_data['trakt_expires_in'],
			'CACHE_LIFE'		=> $post_data['cache_life'],
			'SUB_EXT'			=> $post_data['sub_ext'],
			'MOVIES_FOLDER'		=> $post_data['movies_folder'],
			'LANGUAGE'			=> $post_data['language'],
			'CONFIG_VERSION'	=> $post_data['config_version'],
			'S_LANGUAGE_OPTIONS'	=> $s_lang_options
		));
		$template = new template();
		$template->set_template();
		$template->assign_vars(array(
			'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
			'ERROR'		=> (sizeof($error)) ? '<p class="error">' . implode('<br />', $error) . '</p>' : '',
			'CONTENT'	=> $install->output(),
			'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
		));
		
		page_header($lang['INDEX']);

		$template->set_filename('index_body.html');

		page_footer();
	}
	if($first_run)
	{
		$url = "http://www.mickroz.nl/trakt.php";

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$params = array(
			"referer" => $protocol . $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"],
		);
 
		$request_to = $url . '?' . http_build_query($params);

		header("refresh:5; url=$request_to");
		echo sprintf($lang['FIRST_RUN'], $request_to);
	}
	exit;
}
// Include files
require('includes/cache.php');
require('includes/template.php');
require('includes/functions.php');

// Instantiate some basic classes
$cache		= new cache();
$template	= new template();
$template->set_template();
$cache->cache_time = $cache_life;
set_lang($language);