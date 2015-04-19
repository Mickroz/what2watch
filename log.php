<?php
if (!defined('IN_W2W'))
{
	exit;
}

$error_log = file('error.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$log_lines = '<form method="post" action="?mode=viewlog" id="searchform"> 
	<a href="?mode=viewlog&level=all">All</a> | <a href="?mode=viewlog&level=info">Info</a> | <a href="?mode=viewlog&level=debug">Debug</a>  | <a href="?mode=viewlog&level=error">Error</a> | <a href="?mode=viewlog&level=warning">Warning</a> | <a href="?mode=purge_log">Purge Log</a><br /><br />
	<input type="text" name="message" id="message" maxlength="255" style="width: 200px;" placeholder="{L_SEARCH_FOR}" class="inputbox" />
	<input type="submit" name="submit" value="{L_SEARCH}" class="button" />
	</form>
	<br />';
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
$log_lines .= '<div class="code"><strong>' . (($level == '') ?  'All' : ucfirst($level)) . '</strong></div>';
$log_lines .= '<pre>';
if (empty($view_log))
{
	$info = (!empty($level) ? $level : $search);
	$log_lines .= sprintf($lang['LOG_INFO'], $info);
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