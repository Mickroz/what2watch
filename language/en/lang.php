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
	'ALL'						=> 'All',
	'CACHE_LIFE' 				=> 'Cache life',
	'CACHE_LIFE_EXPLAIN' 				=> 'cache life in seconds',
	'CACHE_PURGED' 				=> 'Cache cleared!',
	'CACHE_PURGED_EXPLAIN' 				=> 'You\'ll be redirected in about 5 secs. If not, click here.',
	'CHECKIN' 				=> 'Check in',
	'CONFIG' 				=> 'Config',
	'CONFIG_NOT_UP_TO_DATE' 				=> 'Config version not up to date, you can update your config here',
	'CONFIG_WRITTEN' 				=> 'Config written',
	'CONFIG_WRITTEN_EXPLAIN' 				=> 'The configuration file has been written, click here to continue.',
	'CREATED_BANNER' 				=> 'Created banner for %s',
	'CREATED_FANART' 				=> 'Creating image from %s',
	'CREATE_CONFIG' 				=> 'Create config',
	'DEBUG'						=> 'Debug',
	'DEBUG_DUMP' 				=> 'Dumping for debug: %s',
	'DL_CONFIG' 				=> 'Download config',
	'DL_CONFIG_EXPLAIN' 				=> 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your root directory. Please remember to upload the file in ASCII format (see your FTP application documentation if you are unsure how to achieve this). When you have uploaded the config.php please click “Done” to move to the next stage.',
	'DL_DONE' 				=> 'Done',
	'DL_DOWNLOAD' 				=> 'Download',
	'DOWNLOAD_BANNER' 				=> 'Download thetvdb.com banner',
	'ERROR'						=> 'Error',
	'FAILED_CHMOD' 				=> 'Failed to set permissions, you should chmod your config file to at least 0644.',
	'FAILED_XML'		=> 'Failed saving xml file from %s for %s',
	'FILL' 				=> 'Paste title',
	'FIRST_RUN' 				=> 'First run, Authorize with trakt first, You\'ll be redirected in about 5 secs. If not, click here.',
	'GET_SLUG' 				=> 'Grabbing slug for %s',
	'GRABBING_FANART' 				=> 'grabbing %s',
	'HTTP_PASSWORD' 				=> 'HTTP Password',
	'HTTP_PASSWORD_EXPLAIN' 				=> 'Password for authentication (blank for none)',
	'HTTP_USERNAME' 				=> 'HTTP Username',
	'HTTP_USERNAME_EXPLAIN' 				=> 'Username for authentication (blank for none)',
	'IGNORE_FOUND' 				=> 'Found %s, ignoring subtitle check for %s',
	'IGNORE_WORDS' 				=> 'Ignore Words',
	'IGNORE_WORDS_EXPLAIN' 				=> 'Case insensitive words separated by , that you wish to ignore in releases.',
	'INDEX' 				=> 'What2Watch',
	'INFO'					=> 'Info',
	'LANGUAGE_SELECT' 				=> 'Select language',
	'LOG' 				=> 'Log',
	'LOG_INFO' 				=> 'There is currently no %s information in your log file!',
	'LOG_PURGED' 				=> 'Log cleared!',
	'LOG_PURGED_EXPLAIN' 				=> 'You\'ll be redirected in about 5 secs. If not, click here.',
	'MESSAGE' 				=> 'Message',
	'MESSAGE_EXPLAIN' 				=> 'Enter a message (optional)',
	'MISSING_LANG_FILES' 				=> 'The iso.txt file missing from the %s language folder.',
	'MOVIES' 				=> 'Movies',
	'MOVIES_FOLDER' 				=> 'Movies folder',
	'MOVIES_FOLDER_EXPLAIN' 				=> 'absolute path, don\'t forget open_basedir settings',
	'NAVIGATION' 				=> 'Navigation',
	'NO_SEASONS_FOUND' 				=> 'No seasons found for %s',
	'NO_SUBTITLE_FOUND' 				=> 'No subtitle was found for %s',
	'OMDB_MOVIE_FAILED'		=> 'Movie not found from OMDBAPI for %s',
	'OPEN_XML' 				=> 'Opening XML %s',
	'OPTIONS' 				=> 'Options',
	'PASSWORD_EMPTY' 				=> 'Password field cannot be empty',
	'PURGE_CACHE' 				=> 'Purge cache',
	'PURGE_LOG'					=> 'Purge log',
	'REFRESH_BANNER' 				=> 'Refresh banner',
	'RESET' 				=> 'Clear',
	'SAVED_BANNER' 				=> 'Saved banner for %s',
	'SAVED_FANART' 				=> 'Saved %s from fanart.tv for %s',
	'SAVED_FANART_FAILED' 				=> 'Failed saving %s from fanart.tv for %s',
	'SAVED_XML'			=> 'Saved xml file from %s for %s',
	'SB_NO_EPISODE' 				=> 'SickBeard API returned no episode data for tvdbid: %s',
	'SB_NO_SHOW' 				=> 'SickBeard API returned nothing for %s',
	'SB_NO_SHOWS' 				=> 'SickBeard API returned no shows',
	'SB_SHOW' 				=> 'SickBeard returned %s',
	'SEARCH' 				=> 'Search',
	'SEARCH_FOR' 				=> 'Search for...',
	'SEASONS_FOUND' 				=> 'Found season %s for %s',
	'SETUP' 				=> 'Setup',
	'SHOWS' 				=> 'Shows',
	'SICKBEARD_API_KEY' 				=> 'Sickbeard API key',
	'SICKBEARD_URL' 				=> 'SickBeard url',
	'SICKBEARD_URL_EXPLAIN' 				=> 'full url i.e. http://localhost:8081',
	'SKIP_FOUND' 				=> 'Found %s, skipping subtitle check for %s',
	'SKIP_SHOWS' 				=> 'Skip Shows',
	'SKIP_SHOWS_EXPLAIN' 				=> 'TVDB id\'s separated by , that you wish to ignore in overview.',
	'SUBMIT' 				=> 'Submit',
	'SUBTITLE_EXTENSION' 				=> 'Subtitle extension',
	'SUBTITLE_EXTENSION_EXPLAIN' 				=> 'with leading period i.e. .nl.srt',
	'SUBTITLE_FOUND' 				=> 'Found a subtitle for %s',
	'TESTING' 				=> 'Testing',
	'TRAKT_CHECKIN' 				=> 'aangemeld bij %s op trakt',
	'TRAKT_ERROR' 				=> 'Communication with trakt is not possible, try again later.',
	'TRAKT_GET_PROGRESS' 				=> 'Trying to get progress for %s',
	'TRAKT_NO_NEXT_EPISODE' 				=> 'Got season folder for %s (%s) but Trakt API returned no next_episode, season finished?',
	'TRAKT_PROGRESS_FAILED' 				=> 'Failed to get progress for %s',
	'TRAKT_PROGRESS_SUCCESS' 				=> 'Trakt returned next episode for %s is %s',
	'USERNAME_EMPTY' 				=> 'Username field cannot be empty',
	'VERSIONCHECK_FAIL' 				=> 'Failed to obtain latest version information.',
	'VERSION_NOT_UP_TO_DATE' 				=> 'New version available : (%s)',
	'VERSION_UP_TO_DATE' 				=> 'What2Watch is up-to-date',
	'WARNING'				=> 'Warning',
	'WELCOME' 				=> 'Welcome to What2Watch, Choose from the menu above',
));

?>