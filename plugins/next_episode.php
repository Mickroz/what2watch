<?php
$log->info('plugins', 'next_episode loaded');

register_filter('the_data','new_data');
 
function new_data($data)
{
	$new_array = array();
	foreach ($data as $episode)
	{
		$key = $episode['tvdbid'];

		$next_episode = getEpisode($key, $episode['season'], $episode['episode_number'] + 1);
		$new_array[$key]['tvdbid'] = $key;
		$new_array[$key]['show_name'] = $episode['show_name'];
		$new_array[$key]['episode'] = $episode['season'] . 'x' . sprintf('%02d', $episode['episode_number'] + 1);
		$new_array[$key]['location'] = $next_episode['data']['location'];

		// Check if there are subs downloaded for this episode
		$check_sub_next = checkSub($new_array, $key);

		if ($check_sub_next)
		{
			$data[$key]['description'] = $data[$key]['description'] . '<br /><small><em>Next: ' . $new_array[$key]['episode'] . ' ' . $next_episode['data']['name'] . '</em></small>';
		}
		unset($new_array);
	}
	
    return $data;
}
?>