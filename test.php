<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
$directory = 'language/';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));

foreach ($scanned_directory as $key => $value) 
{
	$lang_iso = $value;
if (!file_exists("language/$lang_iso/iso.txt"))
{
	trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
}

$file = file("language/$lang_iso/iso.txt");

$lang_pack[$lang_iso] = array(
	'iso'		=> $lang_iso,
	'name'		=> trim(htmlspecialchars($file[0])),
	'local_name'=> trim(htmlspecialchars($file[1], ENT_COMPAT, 'UTF-8'))
);
unset($file);

}

$s_lang_options = '<option class="sep" value="0">' . $lang['SELECT_OPTION'] . '</option>';
foreach ($lang_pack as $iso => $lang)
{
	$selected = ($iso == $data['language']) ? ' selected="selected"' : '';
	$s_lang_options .= '<option value="' . $iso . '"' . $selected . '>' . $lang['name'] . ' ('. $lang['local_name'].')</option>';
}