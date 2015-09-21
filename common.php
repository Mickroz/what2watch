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

$submit	= (isset($_POST['submit'])) ? true : false;
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$page = (isset($_GET['page']) ? $_GET['page'] : '');
$post_data = $lang_pack = $error = array();
$config = $trakt_token = $trakt_expires_in = $trakt_refresh_token = $sickbeard = $sb_api = $cache_life = $sub_ext = $movies_folder = $language = $config_version = $web_username = $web_password = $ignore_words = $skip_shows = $ip_subnet = '';
$download = true;
$debug = 0;
$template_path = 'default';
$language = 'en';

if (file_exists('config.php'))
{
	require('config.php');
}

if (!defined('W2W_INSTALLED') && $mode != 'config_file')
{
	// Redirect the user to the installer
	header('Location: install.php');
	exit;
}
if (!defined('W2W_VERSION'))
{
	include('includes/constants.php');
}
if (($config_version != W2W_VERSION || $mode == 'config') && $mode != 'config_file')
{
	// Fall back to default template on updates
	if ($config_version != W2W_VERSION)
	{
		$template_path = 'default';
	}
	// Redirect the user to the installer
	require('includes/logger.php');
	require('includes/cache.php');
	require('includes/functions.php');
	require('includes/template.php');

	set_lang($language);
	$template	= new template();
	$template->set_template();
	$template->assign_var('META', '');
	$log = new PHPLogger("error.log");
	$cache		= new cache();
    $tag = "INSTALLER";
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
	check_trakt_token();
	
	if($submit)
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

	if(($config_version != W2W_VERSION || $mode == 'config') && $download)
	{
		if ($config_version != W2W_VERSION)
		{
			$error[] = $lang['CONFIG_NOT_UP_TO_DATE'];
		}

		$post_data['sickbeard'] = (isset($_POST['sickbeard']) ? $_POST['sickbeard'] : $sickbeard);
		$post_data['sb_api'] = (isset($_POST['sb_api']) ? $_POST['sb_api'] : $sb_api);
		$post_data['trakt_token'] = (isset($_POST['trakt_token']) ? $_POST['trakt_token'] : $trakt_token);
		$post_data['trakt_expires_in'] = (isset($_POST['trakt_expires_in']) ? $_POST['trakt_expires_in'] : $trakt_expires_in);
		$post_data['trakt_refresh_token'] = (isset($_POST['trakt_refresh_token']) ? $_POST['trakt_refresh_token'] : $trakt_refresh_token);
		$post_data['cache_life'] = (isset($_POST['cache_life']) ? $_POST['cache_life'] : $cache_life);
		$post_data['sub_ext'] = (isset($_POST['sub_ext']) ? $_POST['sub_ext'] : $sub_ext);
		$post_data['movies_folder'] = (isset($_POST['movies_folder']) ? $_POST['movies_folder'] : $movies_folder);
		$post_data['language'] = (isset($_POST['language']) ? $_POST['language'] : $language);
		$post_data['template_path'] = (isset($_POST['template_path']) ? $_POST['template_path'] : $template_path);
		$post_data['web_username'] = (isset($_POST['web_username']) ? $_POST['web_username'] : $web_username);
		$post_data['web_password'] = (isset($_POST['web_password']) ? $_POST['web_password'] : $web_password);
		$post_data['ignore_words'] = (isset($_POST['ignore_words']) ? $_POST['ignore_words'] : $ignore_words);
		$post_data['skip_shows'] = (isset($_POST['skip_shows']) ? $_POST['skip_shows'] : $skip_shows);
		$post_data['ip_subnet'] = (isset($_POST['ip_subnet']) ? $_POST['ip_subnet'] : $ip_subnet);
		$post_data['debug'] = (isset($_POST['debug']) ? $_POST['debug'] : $debug);
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
		
		$s_post_action = 'index.php?mode=config';
		
		$install->assign_vars(array(
			'SICKBEARD'			=> $post_data['sickbeard'],
			'SB_API'			=> $post_data['sb_api'],
			'TRAKT_TOKEN'		=> $post_data['trakt_token'],
			'TRAKT_EXPIRES_IN'	=> $post_data['trakt_expires_in'],
			'TRAKT_REFRESH_TOKEN'	=> $post_data['trakt_refresh_token'],
			'CACHE_LIFE'		=> $post_data['cache_life'],
			'SUB_EXT'			=> $post_data['sub_ext'],
			'MOVIES_FOLDER'		=> $post_data['movies_folder'],
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
			'S_POST_ACTION' 	=> $s_post_action
		));
		$template = new template();
		$template->set_template();
		$template->assign_var('META', '');
		$template->assign_vars(array(
			'CONTENT'	=> $install->output(),
		));
		
		page_header($lang['INDEX']);

		$template->set_filename('index_body.html');

		page_footer();
	}
	exit;
}
// Include files
require('includes/logger.php');
require('includes/cache.php');
require('includes/template.php');
require('includes/functions.php');


// Instantiate some basic classes
$log = new PHPLogger("error.log");
$cache		= new cache();
$template	= new template();
$template->set_template();
$cache->cache_time = $cache_life;
set_lang($language);
$template->assign_var('META', '');
// Add own plugins handler
require('includes/functions_plugins.php');

//Load Plugins
if (!file_exists("plugins/active.json"))
{
    file_put_contents("plugins/active.json", '[]');
}
if ($active_plugins = @file_get_contents("plugins/active.json"))
{
	$config = json_decode($active_plugins, true);
}
foreach($config as $plugin => $value)
{
	require_once("plugins/$plugin.php");
}
check_trakt_token();