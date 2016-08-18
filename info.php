<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/**
* @ignore
*/
if (!defined('IN_W2W'))
{
	exit;
}

include('includes/functions_show.php');

/**
* Loads our layout template, settings its title and content.
*/
$help = new template();
$help->set_template();
$help->set_filename('info_body.html');
$help->assign_vars(array(
	'INSTALL_DIR'		=> dirname(__FILE__),
	'CONFIG_FILE'		=> dirname(__FILE__) . '/config.php',
	'CACHE_FOLDER'		=> dirname(__FILE__) . '/cache',
	'LOG_FOLDER'			=> dirname(__FILE__) . '/logs',
	'PHP_VERSION'		=> phpversion(),
	'W2W_VERSION'		=> $config_version . ' (' . $version['hash'] . ')',
));

$template->assign_vars(array(
	'CONTENT'	=> $help->output(),
));


/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['TESTING']);

$template->set_filename('index_body.html');

page_footer();