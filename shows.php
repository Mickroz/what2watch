<?php
/**
*
* @package What2Watch
* @author Mickroz
* @version Id$
* @link https://www.github.com/Mickroz/what2watch
* @copyright (c) 2015 Mickroz
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_W2W'))
{
	exit;
}
//include files for adding plugin functionality
include('includes/functions_show.php');

// Initial var setup
$series = $data = $showtemplates = $getnext = $hook_before_checkin = $hook_after_checkin = array();
$tag = "Shows";
$checkin = (isset($_GET['checkin'])) ? $_GET['checkin'] : '';
$getbanner = (isset($_GET['getbanner'])) ? $_GET['getbanner'] : '';
$getfanart = (isset($_GET['getfanart'])) ? $_GET['getfanart'] : '';

if ($getbanner)
{
	getBanner($getbanner);
	header('Location: index.php?mode=shows');
}

if ($checkin)
{
	if ($submit)
	{
		$message = $_POST['message'];
		$trakt_id = $_POST['trakt_id'];
		$tvdb_id = $_POST['tvdb_id'];
		$trakt_checkin = trakt_show_checkin($trakt_id, $message);
		$trakt_show_checkin = json_decode($trakt_checkin, true);

		if (!isset($trakt_show_checkin['expires_at']))
		{
			$show_name = $trakt_show_checkin['show']['title'];
			$episode_season = $trakt_show_checkin['episode']['season'];
			$episode_number = sprintf('%02d', $trakt_show_checkin['episode']['number']);
			$episode_short = $episode_season . 'x' . $episode_number;
			$episode_name = $trakt_show_checkin['episode']['title'];
			$show_slug = $trakt_show_checkin['show']['ids']['slug'];
			$log->debug('trakt.tv', sprintf($lang['TRAKT_CHECKIN'], $show_name . ' ' . $episode_short . ' ' . $episode_name));
			$error[] = sprintf($lang['TRAKT_CHECKIN'], $show_name . ' ' . $episode_short . ' ' . $episode_name);
			$get_trakt_info = getTraktId($show_slug, $trakt_show_checkin['episode']['season'], $trakt_show_checkin['episode']['number'] + 1);
			$get_trakt_id = json_decode($get_trakt_info, true);
			$getnext[$tvdb_id]['tvdbid'] = $tvdb_id;
			$getnext[$tvdb_id]['trakt_id'] = $get_trakt_id['ids']['trakt'];
			$getnext[$tvdb_id]['show_name'] = $show_name;
			$getnext[$tvdb_id]['season'] = $episode_season;
			$getnext[$tvdb_id]['episode'] = $get_trakt_id['number'];
			$getnext[$tvdb_id]['episode_name'] = $get_trakt_id['title'];
		}
		else
		{
			$error[] = $lang['TRAKT_ERROR'];
		}
	}
}

if (!empty($getnext))
{
	$log->debug('trakt.tv', $lang['TRAKT_UPDATE']);
	$key = key($getnext);
	$update_show = getShow($key);
	$update_episode = getEpisode($getnext[$key]['tvdbid'], $getnext[$key]['season'], $getnext[$key]['episode']);
	// Put it all in a array
	$update_serie[$key]['tvdbid'] = $key;
	$update_serie[$key]['show_name'] = $update_show[$key]['show_name'];
	$update_serie[$key]['tvrage_id'] = $update_show[$key]['tvrage_id'];
	$update_serie[$key]['show_slug'] = $update_show[$key]['show_slug'];
	$update_serie[$key]['trakt_id'] = $getnext[$key]['trakt_id'];
	$update_serie[$key]['message'] = $getnext[$key]['show_name'] . ' ' . $getnext[$key]['season'] . 'x' . sprintf('%02d', $getnext[$key]['episode']) . ' ' . $getnext[$key]['episode_name'];
	$update_serie[$key]['season'] = $getnext[$key]['season'];
	$update_serie[$key]['episode'] = $getnext[$key]['season'] . 'x' . sprintf('%02d', $getnext[$key]['episode']);
	$update_serie[$key]['episode_number'] = $getnext[$key]['episode'];
	$update_serie[$key]['name'] = $update_episode['data']['name'];
	$update_serie[$key]['description'] = $update_episode['data']['description'];
	$update_serie[$key]['status'] = $update_episode['data']['status'];
	$update_serie[$key]['location'] = $update_episode['data']['location'];
	
	// Check if there are subs downloaded for this episode
	$check_sub_update = checkSub($update_serie, $key);
	$update_serie[$key]['subbed'] = $check_sub_update;
		
	if (!$update_serie[$key]['subbed'])
	{
		$log->debug('checkSub', sprintf($lang['NO_SUBTITLE_FOUND'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
		$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
		if ($data = $cache->get('shows'))
		{
			$data = json_decode($data, true);
			unset($data[$key]);
			$cache->put('shows', json_encode($data));
		}
	}
	else
	{
		if ($data = $cache->get('shows'))
		{
			$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $update_serie[$key]['show_name'] . ' ' . $update_serie[$key]['episode']));
			$data = json_decode($data, true);
			$update_data = array_replace($data, $update_serie);
			$cache->put('shows', json_encode($update_data));
		}
		//unset($getnext,$data);
	}
}

if ($data = $cache->get('shows'))
{
    $data = json_decode($data, true);
}
else
{
	// Lets get started with grabbing all shows from sickbeard
	$shows = getUrl($sickbeard . "/api/" . $sb_api . "/?cmd=shows&sort=name", 'getShows');
	if (!$shows)
	{
		$error[] = $lang['SB_NO_SHOWS'];
		$log->error($tag, $lang['SB_NO_SHOWS']);
	}

	$result = json_decode($shows, true);
	foreach ($result['data'] as $show => $values)
	{
		$tvdbid = $values['tvdbid'];
		$show_id = getShow($tvdbid);

		if(empty($show_id))
		{
			continue;
		}
		$trakt = getProgress($show_id[$tvdbid]['show_slug'], $trakt_token);
		
		$progress = json_decode($trakt, true);
		// We check here if the seasons list is empty, maybe the slug is incorrect
		if(empty($progress['seasons']))
		{
			$log->error('getProgress',  sprintf($lang['TRAKT_PROGRESS_FAILED'], $show_id[$tvdbid]['show_slug']));
			$log->debug('getProgress', sprintf($lang['DEBUG_DUMP'], $trakt));
			continue;
		}
		// We check here if completed is 0, if so, we overwrite all next_episode values with S01E01 values
		// This will fix some problems when trakt returns an empty next_episode for shows not watched yet
		// or shows that return a wrong value in next_episode
		if ($progress['completed'] == 0)
		{
			$getTraktInfo = getTraktId($show_id[$tvdbid]['show_slug'], 1, 1);
			$getTraktID = json_decode($getTraktInfo, true);
			$progress['next_episode']['ids']['trakt'] = $getTraktID['ids']['trakt'];
			$progress['next_episode']['season'] = $getTraktID['season'];
			$progress['next_episode']['number'] = $getTraktID['number'];
			$progress['next_episode']['title'] = $getTraktID['title'];
		}
		if (empty($progress['next_episode']))
		{
			// We expect here that every episode of the show is watched
			if ($progress['completed'] == $progress['aired'])
			{
				$log->debug('getProgress', sprintf($lang['TRAKT_NO_NEXT_EPISODE'], $show_id[$tvdbid]['show_name'], $show_id[$tvdbid]['show_slug']));
				$log->debug('getProgress', sprintf($lang['TRAKT_PROGRESS_COMPLETED'], $progress['completed'], $progress['aired']));
				continue;
			}
			else
			{
				$log->error('getProgress', sprintf($lang['TRAKT_PROGRESS_FAILED'], $show_id[$tvdbid]['show_name'], $show_id[$tvdbid]['show_slug']));
				$log->debug('getProgress', sprintf($lang['DEBUG_DUMP'], $trakt));
				continue;
			}
		}
		$log->info('getProgress', sprintf($lang['TRAKT_PROGRESS_SUCCESS'], $show_id[$tvdbid]['show_name'], $progress['next_episode']['season'] . 'x' . sprintf('%02d', $progress['next_episode']['number'])));
		// Grab all episode data
		$episode = getEpisode($tvdbid, $progress['next_episode']['season'], $progress['next_episode']['number']);
		
		// Put it all in a array
		$series[$tvdbid]['tvdbid'] = $tvdbid;
		$series[$tvdbid]['show_name'] = $show_id[$tvdbid]['show_name'];
		$series[$tvdbid]['tvrage_id'] = $show_id[$tvdbid]['tvrage_id'];
		$series[$tvdbid]['show_slug'] = $show_id[$tvdbid]['show_slug'];
		$series[$tvdbid]['trakt_id'] = $progress['next_episode']['ids']['trakt'];
		$series[$tvdbid]['message'] = $show_id[$tvdbid]['show_name'] . ' ' . $progress['next_episode']['season'] . 'x' . sprintf('%02d', $progress['next_episode']['number']) . ' ' . (!empty($progress['next_episode']['title']) ? $progress['next_episode']['title'] : $episode['data']['name']);
		$series[$tvdbid]['season'] = $progress['next_episode']['season'];
		$series[$tvdbid]['episode'] = $progress['next_episode']['season'] . 'x' . sprintf('%02d', $progress['next_episode']['number']);
		$series[$tvdbid]['episode_number'] = $progress['next_episode']['number'];
		$series[$tvdbid]['name'] = (!empty($progress['next_episode']['title']) ? $progress['next_episode']['title'] : $episode['data']['name']);
		$series[$tvdbid]['description'] = $episode['data']['description'];
		$series[$tvdbid]['status'] = $episode['data']['status'];
		$series[$tvdbid]['location'] = $episode['data']['location'];
	
		// Check if there are subs downloaded for this episode
		$check_sub = checkSub($series, $tvdbid);
		$series[$tvdbid]['subbed'] = $check_sub;
		
		if (!$series[$tvdbid]['subbed'])
		{
			$log->debug('checkSub', sprintf($lang['NO_SUBTITLE_FOUND'], $series[$tvdbid]['show_name'] . ' ' . $series[$tvdbid]['episode']));
			$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $series[$tvdbid]['show_name'] . ' ' . $series[$tvdbid]['episode']));
			unset($series[$tvdbid]);
			$create_image = false;
		}
		else
		{
			$log->info('checkSub', sprintf($lang['CHECK_FINISHED'], $series[$tvdbid]['show_name'] . ' ' . $series[$tvdbid]['episode']));
			$create_image = true;
		}

		if ($create_image)
		{
			$banner = $series[$tvdbid]['tvdbid'] . '.banner.jpg';
			$background = $series[$tvdbid]['tvdbid'] . '.background.jpg';
			$string = $show_id[$tvdbid]['location'];
			$explode = explode( '/', $string );
			$location = str_replace('/' . $explode[3], '', $string);
			
			if (!file_exists(CACHE_IMAGES . '/' . $banner))
			{
				$image = getFanart('tv', $location, $explode[3], $series[$tvdbid]['tvdbid'], $banner, $background);
			
				if ($image['grabbed'] == false)
				{
					$rsr_org = $image['rsr_org'];
					$im = $image['im'];
					$got_bg = $image['got_bg'];
					createImage($series[$tvdbid]['show_name'], $banner, $rsr_org, $im, $got_bg);
				}
			}
			else
			{
				$url = $string . '/' . $banner;
				saveImage($url, $banner, $series[$tvdbid]['show_name']);
			}
		}
	}
	// Save array as json
	$cache->put('shows', json_encode($series));
    $data = $series;
}
if ($getfanart)
{
	$banner = $data[$getfanart]['tvdbid'] . '.banner.jpg';
	unlink(CACHE_IMAGES . '/' . $banner);
	$background = $data[$getfanart]['tvdbid'] . '.background.jpg';
	$string = $data[$getfanart]['location'];
	$explode = explode( '/', $string );
	$location = str_replace('/' . $explode[3], '', $string);
	$image = getFanart('tv', $location, $explode[3], $data[$getfanart]['tvdbid'], $banner, $background);
	
	if ($image['grabbed'] == false)
	{
		$rsr_org = $image['rsr_org'];
		$im = $image['im'];
		$got_bg = $image['got_bg'];
		createImage($data[$getfanart]['show_name'], $banner, $rsr_org, $im, $got_bg);
	}
	header('Location: index.php?mode=shows');
}

// Send $data to plugins
$data = hook_filter('the_data', $data);
$data = hook_filter('hook_before_checkin', $data);
$data = hook_filter('hook_after_checkin', $data);

$count = count($data);
$divider = ceil($count / 2);
$i = 1;
foreach ($data as $show)
{
	if (!array_key_exists('hook_before_checkin', $show) || empty($show['hook_before_checkin']))
	{	
		$show['hook_before_checkin'] = '';
	}
	else
	{
		if (is_array($show['hook_before_checkin']) && !empty($show['hook_before_checkin']))
		{
			$show['hook_before_checkin'] = implode(' &bull; ', $show['hook_before_checkin']);
		}
	}
	if (!array_key_exists('hook_after_checkin', $show) || empty($show['hook_after_checkin']))
	{	
		$show['hook_after_checkin'] = '';
	}
	else
	{
		if (is_array($show['hook_after_checkin']) && !empty($show['hook_after_checkin']))
		{
			$show['hook_after_checkin'] = implode(' &bull; ', $show['hook_after_checkin']);
		}
	}
	
	$row = new template();
	$row->set_template();
	$row->set_filename('list_shows_row.html');
	if ($i == $divider)
	{
		$row->assign_var('BREAK', '</div><div class="col span_1_of_2">');
	}
	else
	{
		$row->assign_var('BREAK', '');
	}	
	foreach ($show as $key => $value)
	{
		$row->assign_var($key, $value);
	}
	
	$showtemplates[] = $row;
	$i++;
}
/**
* Merges all our shows templates into a single variable.
* This will allow us to use it in the main template.
*/

$showcontents = template::merge($showtemplates);

if ($showcontents == '')
{
	$error[] = $lang['CACHE_EMPTY'];
}

$showlist = new template();
$showlist->set_template();
$showlist->set_filename('list_content.html');
$showlist->assign_vars(array(
	'HEADER'	=> $lang['SHOWS'],
	'CONTENT'	=> $showcontents
));

/**
* Loads our layout template, settings its title and content.
*/
$template->assign_vars(array(
	'CONTENT'	=> $showlist->output(),
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['SHOWS']);

$template->set_filename('index_body.html');

page_footer();
