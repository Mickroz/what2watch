<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$series = $data = array();

if ($data = $cache->get('shows'))
{
    $data = json_decode($data, true);
}
else
{
	// Lets get started with grabbing all shows from sickbeard
	$shows = curl($sickbeard . "/api/" . $sb_api . "/?cmd=shows&sort=name");
	if (!$shows)
	{
		$error[] = "SickBeard api returned no shows";
	}

	$result = json_decode($shows, true);

	foreach ($result['data'] as $show => $values)
	{	
		$show_id = curl($sickbeard . "/api/" . $sb_api . "/?cmd=show&tvdbid=" . $values['tvdbid']);
		if (!$show_id)
		{
			$error[] = "SickBeard api returned nothing for" . $values['tvdbid'];
		}
		$result_show = json_decode($show_id, true);
		// Checking which show actually has a episode downloaded
		// and put all  tvdb id's in an array
		// TODO grab naming pattern
		$season_list = $result_show['data']['season_list'];
		foreach ($season_list as $id => $season)
		{
			$padded = sprintf('%02d', $season); 
			$dir = $result_show['data']['location'] . "/Season " .  $padded;
			if (!is_dir($dir))
			{
				continue;
			}
			$show_name[$values['tvdbid']]['show_name'] = $result_show['data']['show_name'];
			$show_name[$values['tvdbid']]['show_slug'] = slugify($result_show['data']['show_name']);
			$show_name[$values['tvdbid']]['tvrage_slug'] = slugify($result_show['data']['tvrage_name']);
		}
	}

	foreach ($show_name as $tvdbid => $value)
	{
		$trakt = get_progress($value['tvrage_slug'], $trakt_token);
		$progress = json_decode($trakt, true);
		// We check here if the seasons list is empty, maybe the slug is incorrect
		if(empty($progress['seasons']))
		{
			// We try the show name slug
			$trakt2 = get_progress($value['show_slug'], $trakt_token);
			$progress2 = json_decode($trakt2, true);
			
			if (empty($progress2['seasons']))
			{
				$error[] = 'Trakt api returned nothing for: ' . $value['show_name'] . '(' . $value['tvrage_slug'] . ' or ' . $value['show_slug'] . ')';
				continue;
			}
			$progress['next_episode'] = $progress2['next_episode'];
		}
		if ($progress['next_episode'] == '')
		{
			continue;
		}
		// Grab all episode data
		$episode = curl($sickbeard . "/api/" . $sb_api . "/?cmd=episode&tvdbid=" . $tvdbid . "&season=" . $progress['next_episode']['season'] . "&episode=" . $progress['next_episode']['number'] . "&full_path=1");
		if (!$episode)
		{
			$error[] = "SickBeard api returned no episode data for tvdbid: $tvdbid";
			continue;
		}
		$result_eps = json_decode($episode, true);
			
		// Remove empty results
		if ($result_eps['result'] == 'failure' || $result_eps['result'] == 'error' || $result_eps['result'] == 'fatal')
		{
			continue;
		}
		// Put it all in a array
		$series[$tvdbid]['tvdbid'] = $tvdbid;
		$series[$tvdbid]['show_name'] = $value['show_name'];
		$series[$tvdbid]['tvrage_slug'] = $value['tvrage_slug'];
		$series[$tvdbid]['show_slug'] = $value['show_slug'];
		$series[$tvdbid]['episode'] = $progress['next_episode']['season'] . 'x' . sprintf('%02d', $progress['next_episode']['number']);
		$series[$tvdbid]['name'] = $progress['next_episode']['title'];
		$series[$tvdbid]['description'] = $result_eps['data']['description'];
		$series[$tvdbid]['status'] = $result_eps['data']['status'];
		$series[$tvdbid]['location'] = $result_eps['data']['location'];
	
		// Check if there are subs downloaded for this episode
		$search = array('.mkv', '.avi', '.mpeg', '.mp4');
		$find_sub = str_replace($search, $sub_ext, $result_eps['data']['location']);
		if (file_exists($find_sub))
		{
			$series[$tvdbid]['subbed'] = true;
		}
		else
		{
			unset($series[$tvdbid]);
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
	// Lets grab the banner
	// First check if the folder exists, if not create it.
	$dir_to_save = __DIR__ . '/images/';
	if (!is_dir($dir_to_save))
	{
		mkdir($dir_to_save);
	}
	if (!file_exists($dir_to_save . $show['tvdbid'] . '.banner.jpg'))
	{
		$banner = file_get_contents($sickbeard . "/api/" . $sb_api . "/?cmd=show.getbanner&tvdbid=" . $show['tvdbid']);
		file_put_contents($dir_to_save . $show['tvdbid'] . '.banner.jpg', $banner);
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