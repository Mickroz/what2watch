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
	'TESTING'			=> 'Testing',
	'CHECKIN'			=> 'Check in',
	'OPTIONS'			=> 'Opties',
	'REFRESH_BANNER'			=> 'Vernieuw banner',
	'DOWNLOAD_BANNER'			=> 'Download thetvdb.com banner',
	'SEARCH_FOR'			=> 'Zoeken naar...',
	'SEARCH'			=> 'Zoeken',
	'LOG_INFO'			=> 'Er is momenteel geen %s informatie in je log bestand!',
	'TRAKT_CHECKIN'		=> 'aangemeld bij %s op trakt',
	'TRAKT_ERROR'		=> 'Communicatie met trakt is niet mogenlijk, probeer het later nog eens.',
	'DEBUG_DUMP'		=> 'Dumpen voor debug: %s',
	'GRABBING_FANART'		=> 'grabbing %s',
	'SAVED_FANART'		=> '%s opgeslagen van fanart.tv voor %s',
	'SAVED_FANART_FAILED'		=> 'Opslaan mislukt van %s van fanart.tv voor %s',
	'CREATED_FANART'		=> 'Afbeelding gecreëerd van %s',
	'CREATED_BANNER'		=> 'Banner gecreëerd voor %s',
	'SAVED_BANNER'			=> 'Banner opgeslagen voor %s',
	'GET_SLUG'				=> 'Grabbing slug voor %s',
	'SB_NO_SHOWS'			=> 'SickBeard API retourneerde shows',
	'SB_NO_SHOW'			=> 'SickBeard API retourneerde niets voor %s',
	'SB_NO_EPISODE'			=> 'SickBeard API retourneerde geen aflevering data voor tvdbid: %s',
	'OPEN_XML'				=> 'Open XML %s',
	'SB_SHOW'			=> 'SickBeard retourneerde %s',
	'NO_SEASONS_FOUND'		=> 'Geen seizoenen gevonden voor %s',
	'SEASONS_FOUND'		=> 'Seizoen %s gevonden voor %s',
        'TRAKT_GET_PROGRESS'    => 'Probeer voortgang te krijgen voor %s',
	'TRAKT_PROGRESS_FAILED'	=> 'Mislukt om de voortgang te krijgen voor %s',
	'TRAKT_PROGRESS_SUCCESS'	=> 'Trakt retourneerde de volgende aflevering voor %s is %s',
	'TRAKT_NO_NEXT_EPISODE'	=> 'Seizoen folder gevonden voor %s (%s) maar Trakt api retourneerde geen next_episode, seizoen klaar?',
	'SUBTITLE_FOUND'		=> 'Ondertitel gevonden voor %s',
	'IGNORE_FOUND'	=> '%s gevonden, negeer ondertitel controle voor %s',
	'SKIP_FOUND'	=> '%s gevonden, sla ontertitel controle over voor %s',
	'NO_SUBTITLE_FOUND'	=> 'Geen ondertitel gevonden voor %s',
	'PASSWORD_EMPTY'	=> 'Het wachtwoordveld mag niet leeg zijn',
	'PASSWORD_EMPTY'	=> 'Het gebruikersnaam veld mag niet leeg zijn',
	'CONFIG_NOT_UP_TO_DATE'	=> 'Config-versie niet up-to-date, hier kan je je config bijwerken',
	'LOG_PURGED'		=> 'Log geleegd!',
	'LOG_PURGED_EXPLAIN'	=> 'Je wordt doorgestuurd in ongeveer 5 seconden, zo niet, klik dan <a href="%s">hier</a>.',
	'NAVIGATION' 		=> 'Navigatie',
	'MOVIES'			=> 'Films',
	'SHOWS'				=> 'Series',
	'CONFIG'			=> 'Config',
	'MESSAGE'			=> 'Message',
	'MESSAGE_EXPLAIN'	=> 'Voer een bericht in (optioneel)',
	'RESET'				=> 'Wissen',
	'FILL'				=> 'Titel plakken',
	'SUBMIT'			=> 'Submit',
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
	'HTTP_USERNAME'		=> 'HTTP Gebruikersnaam',
	'HTTP_USERNAME_EXPLAIN'		=> 'Gebruikersnaam voor authenticatie (leeg voor geen)',
	'HTTP_PASSWORD'		=> 'HTTP Paswoord',
	'HTTP_PASSWORD_EXPLAIN'		=> 'Paswoord voor authenticatie (leeg voor geen)',
	'IGNORE_WORDS'			=> 'Negeer Woorden',
	'IGNORE_WORDS_EXPLAIN'	=> 'Niet hoofdlettergevoelige woorden gescheiden door een , die je wenst te negeren in releases.',
	'SKIP_SHOWS'			=> 'Skip Shows',
	'SKIP_SHOWS_EXPLAIN'	=> 'TVDB id\'s gescheiden door een , die je wenst te negeren in het overzicht.',
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
