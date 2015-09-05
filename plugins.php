<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$plugins = $json_a = $plugintemplates = $update_plugin = array();
$configcontents = $plugin_installed = '';
$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$name = (isset($_GET['name'])) ? $_GET['name'] : '';
// get active plugins
if ($active_plugins = @file_get_contents("plugins/active.json"))
{
	$json_a = $json_b = json_decode($active_plugins, true);
}

foreach( glob("plugins/*.php") as $plugins_detected)
{
	require_once($plugins_detected);
	$plugin = str_replace(array('plugins/', '.php'), '', $plugins_detected);
	$plugin_installed = array_key_exists($plugin, $json_a) ? '<a href="?mode=plugins&name=' . $plugin . '&action=uninstall">' . $lang['PLUGIN_UNINSTALL'] . '</a>' :  '<a href="?mode=plugins&name=' . $plugin . '&action=install">' . $lang['PLUGIN_INSTALL'] . '</a>';
	$plugins[] .= ${$plugin . '_name'} . ' ' . ${$plugin . '_version'} . ' ( ' . $plugin_installed . ' )';
}
if ($submit)
{
	foreach ($_POST as $config_key => $config_val)
	{
		if ($config_key == 'submit')
		{
			continue;
		}
		if ($config_key == 'name')
		{
			continue;
		}
		$update_plugin[$_POST['name']]['config'][$config_key] = $config_val;
	}
	$update_plugin[$_POST['name']]['config']['active'] = true;

	$update_json = array_replace($json_a, $update_plugin);
	$fp = fopen('plugins/active.json', 'w');
	fwrite($fp, json_encode($update_json));
	fclose($fp);
	$redirect_url = "index.php?mode=plugins";
	meta_refresh(3, $redirect_url);
	msg_handler(sprintf($_POST['name'], $lang['PLUGIN_SETTINGS_UPDATED']), 'SUCCESS', 'success');
}

foreach ($json_b as $key => $value)
{
	// If plugin config only has 1 value, we expect it to be the active key, therefor unset it for the settings page
	if (count($json_b[$key]['config']) === 1)
	{
		unset($json_b[$key]);
		continue;
	}
	if (count($json_b) === 0)
	{
		break;
	}
	foreach ($json_a[$key]['config'] as $subkey => $subvalue)
	{
		$list = new template();
		$list->set_template();
		$list->set_filename('list_plugins_row.html');

		if ($subkey == 'active')
		{
			continue;
		}
		// Check for password fields
		$keywords = array('pass', 'passwd', 'password');
		$type = 'text';
		foreach($keywords as $keyword)
		{
			if (strpos($subkey , $keyword) !== false)
			{
				$type = 'password';
			}
		}
		$l_explain = (isset($lang[strtoupper($subkey) . '_EXPLAIN'])) ? $lang[strtoupper($subkey) . '_EXPLAIN'] : '';
		
		$list->assign_vars(array(
			'ID'			=> $key,
			'KEY'			=> $subkey,
			'TYPE'			=> $type,
			'TITLE'			=> (isset($lang[strtoupper($subkey)])) ? $lang[strtoupper($subkey)] : ucfirst(strtolower($subkey)),
			'TITLE_EXPLAIN'	=> $l_explain,
			'CONTENT'		=> $subvalue,
			)
		);
		$plugintemplates[] = $list;
	}
}
$plugincontents = template::merge($plugintemplates);

foreach ($json_b as $plugin_filename => $values)
{
	foreach ($plugintemplates as $cat)
	{
		if ($cat->values['ID'] != $plugin_filename)
		{
			continue 2;
		}
	}

	$configlist = new template();
	$configlist->set_template();
	$configlist->set_filename('list_plugins.html');
	$configlist->assign_vars(array(
		'HEADER'	=> (isset($lang[strtoupper($plugin_filename)])) ? $lang[strtoupper($plugin_filename)] : ucfirst(strtolower($plugin_filename)),
		'NAME'	=> $plugin_filename,
		'CONTENT'	=> $plugincontents
	));
	$configtemplates[] = $configlist;
}
if (!empty($configtemplates))
{
	$configcontents = template::merge($configtemplates);
}
if ($action == 'install')
{
	if (array_key_exists($name, $json_a))
	{
		$msg_title = 'Error';
		$redirect_url = "index.php?mode=plugins";
		meta_refresh(3, $redirect_url);
		msg_handler($lang['PLUGIN_INSTALL_EXIST'], 'ERROR', 'danger');
	}
	else
	{
		include_once ("plugins/" . $name . ".php");
		$json_a[$name]['config'] = ${$name}['config'];
		$fp = fopen('plugins/active.json', 'w');
		fwrite($fp, json_encode($json_a));
		fclose($fp);
		$redirect_url = "index.php?mode=plugins";
		meta_refresh(3, $redirect_url);
		msg_handler(sprintf($lang['PLUGIN_INSTALL_SUCCESS'], $name), 'SUCCESS', 'success');
	}
}

if ($action == 'uninstall')
{
	if (!isset($json_a[$name]))
	{
		$redirect_url = "index.php?mode=plugins";
		meta_refresh(3, $redirect_url);
		msg_handler(sprintf($lang['PLUGIN_UNINSTALL_FAILED'], $name), 'ERROR', 'danger');
	}
	else
	{
		unset($json_a[$name]);
		$fp = fopen('plugins/active.json', 'w');
		fwrite($fp, json_encode($json_a));
		fclose($fp);
		$redirect_url = "index.php?mode=plugins";
		meta_refresh(3, $redirect_url);
		msg_handler(sprintf($lang['PLUGIN_UNINSTALL_SUCCESS'], $name), 'SUCCESS', 'success');
	}
}
/**
* Loads our layout template, settings its title and content.
*/
$pluginlist = new template();
$pluginlist->set_template();
$pluginlist->set_filename('plugins_body.html');
$pluginlist->assign_vars(array(
	'LIST' 	=> (sizeof($plugins)) ? '<strong>' . implode('<br />', $plugins) . '</strong>' : '',
	'CONTENT'	=> $configcontents
));

$template->assign_vars(array(
	'CONTENT'	=> $pluginlist->output(),
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['PLUGINS']);

$template->set_filename('index_body.html');

page_footer();