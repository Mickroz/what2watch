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
	'LOG'				=> 'Log',
	'LOG_PURGED'		=> 'Log geleegd!',
	'LOG_PURGED_EXPLAIN'	=> 'Je wordt doorgestuurd in ongeveer 5 seconden, zo niet, klik dan <a href="%s">hier</a>.',
	'NAVIGATION' 		=> 'Navigatie',
	'MOVIES'			=> 'Films',
	'SHOWS'				=> 'Series',
	'PURGE_CACHE'		=> 'Leeg de buffer',
	'CACHE_PURGED'		=> 'Buffer geleegd!',
	'CACHE_PURGED_EXPLAIN'	=> 'Je wordt doorgestuurd in ongeveer 5 seconden, zo niet, klik dan <a href="%s">hier</a>.',
	'WELCOME'			=> 'Welkom bij What2Watch, Kies uit het menu hierboven',
	'DL_CONFIG'			=> 'Download configuratie',
	'DL_CONFIG_EXPLAIN' => 'Je kunt het config.php bestand naar je computer downloaden, waarna je het manueel uploadt (en het eventueel bestaande config.php bestand overschrijft) naar je root map. Zorg er echter wel voor dat je het bestand in ASCII-formaat uploadt (als je niet weet hoe dit moet, raadpleeg dan de documentatie van je FTP-programma). Nadat je het config.php geüpload hebt, klik je op "klaar" om naar de volgende stap te gaan.',
	'DL_DOWNLOAD'		=> 'Download',
	'CONFIG_WRITTEN'	=> 'Config geschreven',
	'CONFIG_WRITTEN_EXPLAIN'	=> 'Het configuratiebestand is geschreven, klik <a href="index.php">hier</a> om verder te gaan.',
	'DL_DONE'			=> 'Klaar',
	'SETUP'				=> 'Setup',
	'INDEX'				=> 'What2Watch',
	'MISSING_LANG_FILES'			=> 'Het iso.txt bestand mist in de %s language folder.',
	'VERSIONCHECK_FAIL'			=> 'Verkrijgen van laatste versie-informatie mislukt.',
	'VERSION_UP_TO_DATE'		=> 'What2Watch is up-to-date',
	'VERSION_NOT_UP_TO_DATE'	=> 'Nieuwe versie beschikbaar : (%s)',
	'FAILED_CHMOD'				=> 'Mislukt om permissies in te stellen, je moet je configuratiebestand permissies tenminste veranderen naar 0644.',
	'SICKBEARD_URL'					=> 'SickBeard url',
	'SICKBEARD_URL_EXPLAIN'			=> 'volledige url http://localhost:8081',
	'SICKBEARD_API_KEY'				=> 'Sickbeard Api key',
	'CACHE_LIFE'					=> 'Buffer tijd',
	'CACHE_LIFE_EXPLAIN'			=> 'buffer tijd in seconden',
	'SUBTITLE_EXTENSION'			=> 'Ondertitels extensie',
	'SUBTITLE_EXTENSION_EXPLAIN'	=> 'met voorloop punt bijvoorbeeld .nl.srt',
	'MOVIES_FOLDER'					=> 'Films folder',
	'MOVIES_FOLDER_EXPLAIN'			=> 'absolute pad, vergeet de open_basedir instelling niet',
	'CREATE_CONFIG'					=> 'Creër configuratie',
	'LANGUAGE_SELECT'				=> 'Taal',
	'FIRST_RUN'						=> 'Eerste keer, Authoriseer eerst bij trakt, Je wordt doorgestuurd in ongeveer 5 seconden, zo niet, klik dan <a href="%s">hier</a>.',
));

?>