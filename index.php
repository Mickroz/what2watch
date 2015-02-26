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
$error = array();
$version = version_check();

switch ($mode)
{
	case 'shows':
		include('shows.php');
	break;
	
	case 'movies':
		include('movies.php');
	break;
	
	case 'purge_cache':
		$cache->purge();
		$referer = $_SERVER['HTTP_REFERER'];
		header("refresh:5; url=" . $referer); 
		$error[] = $lang['CACHE_PURGED'];
		$cache_message = sprintf($lang['CACHE_PURGED_EXPLAIN'], $referer);
	
	default:
		/**
		* Loads our layout template, settings its title and content.
		*/
		$content = (isset($cache_message) ? $cache_message : $lang['WELCOME']);

		$template->assign_vars(array(
			'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
			'CONTENT'	=> $content,
			'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
			'ERROR'		=> (sizeof($error)) ? '<strong style="color:red">' . implode('<br />', $error) . '</strong>' : '',
		));
		/**
		* Finally we can output our final page.
		*/
		page_header($lang['INDEX']);

		$template->set_filename('index_body.html');

		page_footer();
}