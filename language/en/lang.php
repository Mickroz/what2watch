<?php
/**
* DO NOT CHANGE
*/
if (!defined('IN_W2W'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NAVIGATION' 		=> 'Navigation',
	'MOVIES'			=> 'Movies',
	'SHOWS'				=> 'Shows',
	'PURGE_CACHE'		=> 'Purge cache',
	'CACHE_PURGED'		=> 'Cache cleared!',
	'CACHE_PURGED_EXPLAIN'	=> 'You\'ll be redirected in about 5 secs. If not, click <a href="%s">here</a>.',
	'WELCOME'			=> 'Welcome to What2Watch, Choose from the menu above',
	'DL_CONFIG'			=> 'Download config',
	'DL_CONFIG_EXPLAIN' => 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your root directory. Please remember to upload the file in ASCII format (see your FTP application documentation if you are unsure how to achieve this). When you have uploaded the config.php please click “Done” to move to the next stage.',
	'DL_DOWNLOAD'		=> 'Download',
	'CONFIG_WRITTEN'	=> 'Config written',
	'CONFIG_WRITTEN_EXPLAIN'	=> 'The configuration file has been written, click <a href="index.php">here</a> to continue.',
	'DL_DONE'			=> 'Done',
	'SETUP'				=> 'Setup',
	'INDEX'				=> 'What2Watch',
	'MISSING_LANG_FILES'			=> 'The iso.txt file missing from the %s language folder.',
	'VERSIONCHECK_FAIL'			=> 'Failed to obtain latest version information.',
	'VERSION_UP_TO_DATE'		=> 'What2Watch is up-to-date',
	'VERSION_NOT_UP_TO_DATE'	=> 'New version available : (%s)',
	'FAILED_CHMOD'				=> 'Failed to set permissions, you should chmod your config file to at least 0644.',
	'SICKBEARD_URL'					=> 'SickBeard url',
	'SICKBEARD_URL_EXPLAIN'			=> 'full url i.e. http://localhost:8081',
	'SICKBEARD_API_KEY'				=> 'Sickbeard Api key',
	'CACHE_LIFE'					=> 'Cache life',
	'CACHE_LIFE_EXPLAIN'			=> 'cache life in seconds',
	'SUBTITLE_EXTENSION'			=> 'Subtitle extension',
	'SUBTITLE_EXTENSION_EXPLAIN'	=> 'with leading period i.e. .nl.srt',
	'MOVIES_FOLDER'					=> 'Movies folder',
	'MOVIES_FOLDER_EXPLAIN'			=> 'absolute path, don\'t forget open_basedir settings',
	'CREATE_CONFIG'					=> 'Create config',
	'LANGUAGE_SELECT'				=> 'Select language',
	'FIRST_RUN'						=> 'First run, Authorize with trakt first, You\'ll be redirected in about 5 secs. If not, click <a href="%s">here</a>.',
));

?>