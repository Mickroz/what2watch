<?php
if (!defined('IN_W2W'))
{
	exit;
}

$error_log = file('error.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$log_lines = '';
if ($submit)
{
	header('Location: index.php?mode=viewlog&search=' . $_POST['message']);
}
$level = (isset($_GET['level'])) ? $_GET['level'] : '';
$search = (isset($_GET['search'])) ? $_GET['search'] : '';
$view_log = '';
$error_log = array_reverse($error_log);
$count = 1;
foreach ($error_log as $key => $val)
{
	if ($count == 500)
	{
		break;
	}
	if (!$search)
	{
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
	if ($search)
	{
		if (strpos($val, $search) !== false)
		{
			$view_log .= $val . "\r\n";
			$count++;
		}
	}
}

if (empty($view_log))
{
	$log_info = (!empty($level) ? $level : $search);
	$log_lines .= sprintf($lang['LOG_INFO'], $log_info);
}
else
{
	$log_lines .= $view_log;
}

/**
* Loads our layout template, settings its title and content.
*/
$loglist = new template();
$loglist->set_template();
$loglist->set_filename('log_body.html');
$loglist->assign_vars(array(
	'LEVEL'		=> (($level == '') ?  '{L_ALL}' : ucfirst($level)),
	'CONTENT' 	=> $log_lines,
));

$template->assign_vars(array(
	'CONTENT'	=> $loglist->output(),
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['LOG']);

$template->set_filename('index_body.html');

page_footer();