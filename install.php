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
define('IN_W2W', true);

$submit	= (isset($_POST['submit'])) ? true : false;
$post_data = $lang_pack = $error = array();
$trakt_token = $trakt_expires_in = $trakt_refresh_token = $sickbeard = $sb_api = $cache_life = $sub_ext = $movies_folder = $language = $config_version = $web_username = $web_password = $ignore_words = $skip_shows = $ip_subnet = $tvdb_token = '';
$download = true;
$template_path = 'default';
$language = 'en';
$log_filesize = 1024;

if (file_exists('config.php'))
{
	require('config.php');
}

if (defined('W2W_INSTALLED'))
{
	// Redirect the user to the main page
	header('Location: index.php');
	exit;
}

// Include files
include('includes/logger.php');
include('includes/cache.php');
include('includes/template.php');
include('includes/functions.php');
include('includes/constants.php');

set_lang($language);
$template	= new template();
$template->set_template();
$log = new PHPLogger("error.log");
$cache		= new cache();
$tag = "INSTALLER";
$debug = 0;
if ($version = $cache->get('version_check'))
{
	$version = json_decode($version, true);
}
else
{
	$version = version_check();
	// Save array as json
	$cache->put('version_check', json_encode($version));
}
if (empty($tvdb_token))
{
	include('include/functions_show.php');
	$thetvdb_api = tvdb_get_token();
	if (array_key_exists("token", $thetvdb_api))
	{
		$tvdb_token = $thetvdb_api['token'];
	}
}

if ($submit)
{
	if (!empty($_POST['web_username']) && empty($_POST['web_password']))
	{
		$error[] = $lang['PASSWORD_EMPTY'];
	}
	if (empty($_POST['web_username']) && !empty($_POST['web_password']))
	{
		$error[] = $lang['USERNAME_EMPTY'];
	}
	if (!sizeof($error))
	{
		create_config_file();
		$download = false;
	}
}

if ((isset($_GET['access_token']) && !empty($_GET['access_token'])) && $download)
{
	$post_data['sickbeard'] = (isset($_POST['sickbeard']) ? $_POST['sickbeard'] : $sickbeard);
	$post_data['sb_api'] = (isset($_POST['sb_api']) ? $_POST['sb_api'] : $sb_api);
	$post_data['trakt_token'] = (isset($_POST['trakt_token']) ? $_POST['trakt_token'] : (isset($_GET['access_token']) ? $_GET['access_token'] : $trakt_token));
	$post_data['trakt_expires_in'] = (isset($_POST['trakt_expires_in']) ? $_POST['trakt_expires_in'] : (isset($_GET['expires_in']) ? $_GET['expires_in'] : $trakt_expires_in));
	$post_data['trakt_refresh_token'] = (isset($_POST['trakt_refresh_token']) ? $_POST['trakt_refresh_token'] : (isset($_GET['refresh_token']) ? $_GET['refresh_token'] : $trakt_refresh_token));
	$post_data['cache_life'] = (isset($_POST['cache_life']) ? $_POST['cache_life'] : $cache_life);
	$post_data['sub_ext'] = (isset($_POST['sub_ext']) ? $_POST['sub_ext'] : $sub_ext);
	$post_data['language'] = (isset($_POST['language']) ? $_POST['language'] : $language);
	$post_data['template_path'] = (isset($_POST['template_path']) ? $_POST['template_path'] : $template_path);
	$post_data['web_username'] = (isset($_POST['web_username']) ? $_POST['web_username'] : $web_username);
	$post_data['web_password'] = (isset($_POST['web_password']) ? $_POST['web_password'] : $web_password);
	$post_data['ignore_words'] = (isset($_POST['ignore_words']) ? $_POST['ignore_words'] : $ignore_words);
	$post_data['skip_shows'] = (isset($_POST['skip_shows']) ? $_POST['skip_shows'] : $skip_shows);
	$post_data['ip_subnet'] = (isset($_POST['ip_subnet']) ? $_POST['ip_subnet'] : $ip_subnet);
	$post_data['debug'] = (isset($_POST['debug']) ? $_POST['debug'] : $debug);
	$post_data['log_filesize'] = (isset($_POST['log_filesize']) ? $_POST['log_filesize'] : $log_filesize);
	$post_data['tvdb_token'] = (isset($_POST['tvdb_token']) ? $_POST['tvdb_token'] : $tvdb_token);
	$post_data['config_version'] = W2W_VERSION;
		
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
		
	$dir = 'styles/';
	$scanned_dir = array_diff(scandir($dir), array('..', '.'));
	$s_style_options = '';
	foreach ($scanned_dir as $key => $style) 
	{
		$selected = ($style == $post_data['template_path']) ? ' selected="selected"' : '';
		$s_style_options .= '<option value="' . $style . '"' . $selected . '>' . $style . '</option>';
	}
		
	$install = new template();
	$install->set_template();
	$install->set_filename('install_body.html');
	
	$s_post_action = 'install.php';
		
	$install->assign_vars(array(
		'SICKBEARD'			=> $post_data['sickbeard'],
		'SB_API'			=> $post_data['sb_api'],
		'TRAKT_TOKEN'		=> $post_data['trakt_token'],
		'TRAKT_EXPIRES_IN'	=> $post_data['trakt_expires_in'],
		'TRAKT_REFRESH_TOKEN'	=> $post_data['trakt_refresh_token'],
		'CACHE_LIFE'		=> $post_data['cache_life'],
		'SUB_EXT'			=> $post_data['sub_ext'],
		'WEB_USERNAME'		=> $post_data['web_username'],
		'WEB_PASSWORD'		=> $post_data['web_password'],
		'IGNORE_WORDS'		=> $post_data['ignore_words'],
		'SKIP_SHOWS'		=> $post_data['skip_shows'],
		'LANGUAGE'			=> $post_data['language'],
		'CONFIG_VERSION'	=> $post_data['config_version'],
		'S_LANGUAGE_OPTIONS'	=> $s_lang_options,
		'S_STYLE_OPTIONS'	=> $s_style_options,
		'IP_SUBNET'			=> $ip_subnet,
		'DEBUG'			=> ($debug) ? ' checked' : '',
		'LOG_FILESIZE'			=> $post_data['log_filesize'],
		'TVDB_TOKEN'			=> $post_data['tvdb_token'],
		'S_POST_ACTION' 	=> $s_post_action
	));
	
	$template = new template();
	$template->set_template();
	$template->assign_vars(array(
		'CONTENT'	=> $install->output(),
		'META'	=>  ''
	));
		
	page_header($lang['INDEX']);

	$template->set_filename('index_body.html');

	page_footer();
}

if (empty($_GET['access_token']))
{
	// CHANGE THIS TO YOUR OWN URL
	$url = "http://www.link.to/trakt.php";

	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$params = array(
		"referer" => $protocol . $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"],
	);
 
	$request_to = $url . '?' . http_build_query($params);

	header("refresh:5; url=$request_to");
	echo sprintf($lang['FIRST_RUN'], '<a href="' . $request_to . '">' . $lang['HERE'] . '</a>');
}

?>