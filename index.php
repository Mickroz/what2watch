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

// Status flag:
$LoginSuccessful = false;

if (!empty($ip_subnet))
{
	if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($ip_subnet)) == $ip_subnet)
	{
		unset($web_username);
	}
}

if (!empty($web_username))
{
	// Check username and password:
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
	{
 
		$Username = $_SERVER['PHP_AUTH_USER'];
		$Password = $_SERVER['PHP_AUTH_PW'];
 
		if ($Username == $web_username && $Password == $web_password)
		{
			$LoginSuccessful = true;
		}
	}
}
else
{
	$LoginSuccessful = true;
}

// Login passed successful?
if (!$LoginSuccessful){
 
    /* 
    ** The user gets here if:
    ** 
    ** 1. The user entered incorrect login data (three times)
    **     --> User will see the error message from below
    **
    ** 2. Or the user requested the page for the first time
    **     --> Then the 401 headers apply and the "login box" will
    **         be shown
    */
 
    // The text inside the realm section will be visible for the 
    // user in the login box
    header('WWW-Authenticate: Basic realm="What2Watch"');
    header('HTTP/1.0 401 Unauthorized');
 
    print "Login failed!\n";
 
}
else
{

switch ($mode)
{
	case 'shows':
		include('shows.php');
	break;
	
	case 'movies':
		include('movies.php');
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
}