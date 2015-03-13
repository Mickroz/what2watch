<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$series = $data = array();
$tag = "Shows";

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
		$error[] = "SickBeard api returned no shows";
		$log->error($tag, "SickBeard api returned no shows");
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
			$log->error('getProgress',  "Failed to get progress for " . $show_id[$tvdbid]['show_slug']);
			$log->debug('getProgress', 'dumping for debug ' . $trakt);
		}
		if ($progress['next_episode'] == '')
		{
			$error[] = 'Trakt api returned nothing for: ' . $show_id[$tvdbid]['show_name'] . '(' . $show_id[$tvdbid]['show_slug'] . ')';
			$log->error('getProgress', 'Trakt api returned nothing for: ' . $show_id[$tvdbid]['show_name'] . '(' . $show_id[$tvdbid]['show_slug'] . ')');
			$log->debug('getProgress', 'dumping for debug ' . $trakt);
			continue;
		}
		// Grab all episode data
		$episode = getEpisode($tvdbid, $progress['next_episode']['season'], $progress['next_episode']['number']);
		
		// Put it all in a array
		$series[$tvdbid]['tvdbid'] = $tvdbid;
		$series[$tvdbid]['show_name'] = $show_id[$tvdbid]['show_name'];
		//$series[$tvdbid]['tvrage_slug'] = $show_id[$tvdbid]['tvrage_slug'];
		$series[$tvdbid]['show_slug'] = $show_id[$tvdbid]['show_slug'];
		$series[$tvdbid]['episode'] = $progress['next_episode']['season'] . 'x' . sprintf('%02d', $progress['next_episode']['number']);
		$series[$tvdbid]['name'] = $progress['next_episode']['title'];
		$series[$tvdbid]['description'] = $episode['data']['description'];
		$series[$tvdbid]['status'] = $episode['data']['status'];
		$series[$tvdbid]['location'] = $episode['data']['location'];
	
		// Check if there are subs downloaded for this episode
		$search = array('.mkv', '.avi', '.mpeg', '.mp4');
		$find_sub = str_replace($search, $sub_ext, $episode['data']['location']);
		if (file_exists($find_sub))
		{
			$log->debug('checkSub', "found a subtitle for " . $series[$tvdbid]['show_name'] . ' ' . $series[$tvdbid]['episode']);
			$series[$tvdbid]['subbed'] = true;
			$create_image = true;
		}
		else
		{
			$log->debug('checkSub', "no subtitle was found for " . $series[$tvdbid]['show_name'] . ' ' . $series[$tvdbid]['episode']);
			unset($series[$tvdbid]);
			$create_image = false;
		}
		if ($create_image)
		{
			$banner = $series[$tvdbid]['tvdbid'] . '.banner.jpg';
			$background = $series[$tvdbid]['tvdbid'] . '.background.jpg';
			$string = $show_id[$tvdbid]['location'];
			$explode = explode( '/', $string );
			$location = str_replace('/' . $explode[3], '', $string);
			
			if (!file_exists($string . '/' . $banner))
			{
				$image = getFanart('tv', $location, $explode[3], $series[$tvdbid]['tvdbid'], $banner, $background);
			
				if ($image['grabbed'] == false)
				{
					$rsr_org = $image['rsr_org'];
					$im = $image['im'];
					$got_bg = $image['got_bg'];
					createImage($location, $explode[3], $series[$tvdbid]['show_name'], $banner, $rsr_org, $im, $got_bg);
				}
			}
			$url = $string . '/' . $banner;
			saveImage($url, $banner, $series[$tvdbid]['show_name']);
		}
	}
	// Save array as json
	$cache->put('shows', json_encode($series));
    $data = $series;
}
$count = count($data);
$divider = ceil($count / 2);
$i = 1;
foreach ($data as $show)
{
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

$showlist = new template();
$showlist->set_template();
$showlist->set_filename('list_content.html');
$showlist->assign_var('CONTENT', $showcontents);

/**
* Loads our layout template, settings its title and content.
*/
$template->assign_vars(array(
	'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
	'CONTENT'	=> $showlist->output(),
	'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
	'ERROR'		=> (sizeof($error)) ? '<strong style="color:red">' . implode('<br />', $error) . '</strong>' : '',
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['SHOWS']);

$template->set_filename('index_body.html');

page_footer();