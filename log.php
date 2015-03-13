<?php
if (!defined('IN_W2W'))
{
	exit;
}

$error_log = file('error.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$log_lines = '<a href="?mode=viewlog&level=all">All</a> | <a href="?mode=viewlog&level=info">Info</a> | <a href="?mode=viewlog&level=debug">Debug</a>  | <a href="?mode=viewlog&level=error">Error</a> | <a href="?mode=viewlog&level=warning">Warning</a> | <a href="?mode=purge_log">Purge Log</a><br /><br />';
$level = (isset($_GET['level'])) ? $_GET['level'] : '';
$view_log = '';
$error_log = array_reverse($error_log);
$count = 1;
foreach ($error_log as $key => $val)
{
	if ($count == 500)
	{
		break;
	}
	if ($level == '' || $level == 'all')
	{
		$view_log .= $val . "\r\n";
		$count++;
		continue;
	}
	if (strpos($val, strtoupper($level)) !== false)
	{
		$view_log .= $val . "\r\n";
		$count++;
	}
}
$log_lines .= '<div class="code"><strong>' . (($level == '') ?  'All' : ucfirst($level)) . '</strong></div>';
$log_lines .= '<pre>';
if (empty($view_log))
{
	$log_lines .= "There is currently no $level information in your log file!";
}
else
{
	$log_lines .= $view_log;
}
$log_lines .= '</pre>';
/**
* Loads our layout template, settings its title and content.
*/
$template->assign_vars(array(
	'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
	'CONTENT'	=> $log_lines,
	'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
	'ERROR'		=> (sizeof($error)) ? '<strong style="color:red">' . implode('<br />', $error) . '</strong>' : '',
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['LOG']);

$template->set_filename('index_body.html');

page_footer();