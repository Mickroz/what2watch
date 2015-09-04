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

$test_lines = '<div class="code"><strong>{L_TESTING}</strong></div>';
$test_lines .= '<pre>';
// Output from tests go here
// Uncomment below
// $test_lines .= your code;

$test_lines .= '</pre>';
/**
* Loads our layout template, settings its title and content.
*/
$template->assign_vars(array(
	'CONTENT'	=> $test_lines,
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['TESTING']);

$template->set_filename('index_body.html');

page_footer();