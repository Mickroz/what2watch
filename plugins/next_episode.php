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

// Should be named exactly like filename
$next_episode_name = 'Next Episode';
$next_episode_version = '1.0.1';
$next_episode['config'] = array(
	'active'	=> true
);

$log->info('plugins', 'next_episode loaded');

register_filter('the_data','new_data');
 
function new_data($data)
{
	global $log, $lang;
	
	$new_array = array();
	$i = 0;
	foreach ($data as $episode)
	{
		if ($i == 0)
		{
			$log->info('-------', $lang['SEPARATOR']);
		}
		$i++;
		$log->info('nextEpisode', $lang['NEXT_EPISODE_START']);
		$log->info('nextEpisode', sprintf($lang['NEXT_EPISODE_CHECK'], $episode['show_name'] . ' ' . $episode['season'] . 'x' . sprintf('%02d', $episode['episode_number'] + 1)));
		$key = $episode['tvdbid'];

		$next_episode = getEpisode($key, $episode['season'], $episode['episode_number'] + 1);
		$new_array[$key]['tvdbid'] = $key;
		$new_array[$key]['show_name'] = $episode['show_name'];
		$new_array[$key]['episode'] = $episode['season'] . 'x' . sprintf('%02d', $episode['episode_number'] + 1);
		$new_array[$key]['location'] = $next_episode['data']['location'];

		// Check if there are subs downloaded for this episode
		$check_sub_next = file_exists($next_episode['data']['location']) ? checkSub($new_array, $key) : '';

		if ($check_sub_next)
		{
			$data[$key]['description'] = $data[$key]['description'] . '<br /><small><em>' . $lang['NEXT'] . ': ' . $new_array[$key]['episode'] . ' ' . $next_episode['data']['name'] . '</em></small>';
		}
		unset($new_array);
		$log->info('nextEpisode', $lang['NEXT_EPISODE_END']);
		$log->info('-------', $lang['SEPARATOR']);
		
	}
	
    return $data;
}
?>