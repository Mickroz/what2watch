<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/**
* @ignore
*/
define('IN_W2W', true);
include('common.php');

// Initial var setup
$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');
$error = $success = $info = $warning = array();
$purge_cache = false;
$user_ip = $_SERVER['REMOTE_ADDR'];
$validated = false;
$hash = md5($user_ip . $web_password . $random2);
$self = $_SERVER['REQUEST_URI'];

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

/* USER IS LOGGED IN */
if (isset($_COOKIE[$random1 . '_l']))
{
	// Separate selector from validator.
	list($selector, $validator) = explode(":", $_COOKIE[$random1 . '_l']);
	if ($selector == $random2 && $validator == $hash)
	{
		$validated = true;
	}
}

if (!empty($ip_subnet))
{
	if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($ip_subnet)) == $ip_subnet)
	{
		$validated = true;
	}
}

/* FORM HAS BEEN SUBMITTED */
// Login passed successful?
if (!$validated)
{
	$mode = 'login';
}

switch ($mode)
{
	case 'login':
		include('login.php');
	break;
	
	case 'shows':
		include('shows.php');
	break;
	
	case 'viewlog':
		include('log.php');
	break;

	case 'config_file':
		create_config_file();
		header('Location: index.php');
	break;
	
	case 'info':
		include('info.php');
	break;

	case 'plugins':
		include('plugins.php');
	break;
	
	case 'test':
		include('test.php');
	break;
	
	case 'purge_cache':
		$cache->purge();
		$log->info('Cache', $lang['CACHE_PURGED']);

		$redirect_url = $_SERVER['HTTP_REFERER'];
		meta_refresh(5, $redirect_url);
		msg_handler(sprintf($lang['CACHE_PURGED_EXPLAIN'], '<a href="' . $redirect_url . '">' . $lang['HERE'] . '</a>'), 'CACHE_PURGED', 'info');
	break;
	
	case 'purge_log':
		$lines_array = file($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/what2watch.log');
		$new_output = "";
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/what2watch.log', $new_output);
		$log->info('Log', $lang['LOG_PURGED']);

		$redirect_url = $_SERVER['HTTP_REFERER'];
		meta_refresh(5, $redirect_url);
		msg_handler(sprintf($lang['LOG_PURGED_EXPLAIN'], '<a href="' . $redirect_url . '">' . $lang['HERE'] . '</a>'), 'LOG_PURGED', 'info');
	break;
	
	default:
		/**
		* Loads our layout template, settings its title and content.
		*/

		$template->assign_vars(array(
			'CONTENT'	=> $lang['WELCOME'],
		));
		/**
		* Finally we can output our final page.
		*/
		page_header($lang['INDEX'] . ' - You choose what 2 watch');

		$template->set_filename('index_body.html');

		page_footer();
}