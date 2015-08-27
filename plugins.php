<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$plugins = array();

foreach( glob("plugins/*.php") as $plugins_detected)
{
	$plugin = str_replace(array('plugins/', '.php'), '', $plugins_detected);
	$plugins[] .= ${$plugin . '_name'} . ' ' . ${$plugin . '_version'};
}

/**
* Loads our layout template, settings its title and content.
*/
$pluginlist = new template();
$pluginlist->set_template();
$pluginlist->set_filename('plugins_body.html');
$pluginlist->assign_vars(array(
	'CONTENT' 	=> (sizeof($plugins)) ? '<strong>' . implode('<br />', $plugins) . '</strong>' : '',
));

$template->assign_vars(array(
	'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
	'CONTENT'	=> $pluginlist->output(),
	'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
	'ERROR'		=> (sizeof($error)) ? '<strong style="color:red">' . implode('<br />', $error) . '</strong>' : '',
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['PLUGINS']);

$template->set_filename('index_body.html');

page_footer();