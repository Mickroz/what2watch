<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$movies = $data = array();
$tag = "Movies";

if ($data = $cache->get('movies'))
{
    $data = json_decode($data, true);
}
else
{
	$scanned_directory = array_diff(scandir($movies_folder), array('..', '.', 'folder.jpg'));

	foreach ($scanned_directory as $key => $value) 
	{		
		if ($handle = opendir($movies_folder . '/' . $value)) {

			while (false !== ($file = readdir($handle)))
			{
				$search = array('.mkv', '.avi', '.mpeg', '.mp4');
				$filename = str_replace($search, '.xml', $file);
				if (!file_exists($movies_folder . '/' . $value . '/' . $filename))
				{
					createXml($filename);
				}
				
				if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'xml')
				{
					$array = array(
						'movieid' 	=> '',
						'title' 	=> '',
						'runtime' 	=> '',
						'year' 		=> '',
						'mpaa' 		=> '',
						'plot' 		=> '',
						'genre' 	=> '',
						'banner' 	=> '',
					);
					$movie = simplexml_load_file($movies_folder . '/' . $value . '/' . $file);
					$log->info('openXml', 'Opening XML ' . $movies_folder . '/' . $value . '/' . $file);
					$json = json_encode($movie);
					$array = json_decode($json, true);
					$array = array_change_key_case($array, CASE_LOWER);
					$movie_id = (isset($array['id'])) ? $array['id'] : $array['imdbid'];
					$banner = str_replace('.xml', '.banner.jpg', $file);
					$background = str_replace('.xml', '.background.jpg', $file);
					if (!file_exists($movies_folder . '/' . $value . '/' . $banner))
					{
						$image = getFanart('movies', $movies_folder, $value, $movie_id, $banner, $background);
						if ($image['grabbed'] == false)
						{
							$rsr_org = $image['rsr_org'];
							$im = $image['im'];
							$got_bg = $image['got_bg'];
							createImage($movies_folder, $value, $movie->title, $banner, $rsr_org, $im, $got_bg);
						}
					}
					$url = $movies_folder . '/' . $value . '/' . $banner;
					saveImage($url, $banner, $movie->title);
					
					if (!isset($array['mpaa']))
					{
						$array['mpaa'] = 'Not Rated';
					}
					$moviemeter = file_get_contents("http://mickroz.nl/moviemeter.php?imdbid=" . $movie_id);
					$log->info('movieMeterAPI', 'Opening URL http://mickroz.nl/moviemeter.php?imdbid=' . $movie_id);
					if (strpos($moviemeter, 'error:') !== 0)
					{
						$plot = utf8_encode($moviemeter);
					}
					else
					{
						$plot = $array['plot'];
					}
					if (!isset($plot))
					{
						$plot = 'No plot';
					}
					$movies[$movie_id]['movieid'] = $movie_id;
					$movies[$movie_id]['title'] = $array['title'];
					$movies[$movie_id]['runtime'] = $array['runtime'];
					$movies[$movie_id]['year'] = $array['year'];
					$movies[$movie_id]['mpaa'] = $array['mpaa'];
					$movies[$movie_id]['plot'] = $plot;
					$movies[$movie_id]['genre'] = is_array($array['genre']) ? implode(",", $array['genre']) : $array['genre'];
					$movies[$movie_id]['banner'] = $banner;

					if (empty($movie_id) || empty($array['title']) || empty($array['runtime']) || empty($array['year']) || empty($array['genre']) || empty($banner))
					{
						$log->debug($tag, 'Dumping movie info for debug ' . json_encode($movies[$movie_id]));
					}
				}
			}
			closedir($handle);
		}
	}
	// Save array as json
	$cache->put('movies', json_encode($movies));
    $data = $movies;
}
$count = count($data);
$divider = ceil($count / 2);
$i = 1;
foreach ($data as $film)
{
	$row = new template();
	$row->set_template();
	$row->set_filename('list_movies_row.html');
	if ($i == $divider)
	{
		$row->assign_var('BREAK', '</div><div class="col span_1_of_2">');
	}
	else
	{
		$row->assign_var('BREAK', '');
	}
	foreach ($film as $key => $value)
	{
		$row->assign_var($key, $value);
	}
	$moviestemplates[] = $row;
	$i++;
}
/**
* Merges all our movies templates into a single variable.
* This will allow us to use it in the main template.
*/
$moviescontents = '';
$moviescontents = template::merge($moviestemplates);

$movieslist = new template();
$movieslist->set_template();
$movieslist->set_filename('list_content.html');
$movieslist->assign_var('CONTENT', $moviescontents);
/**
* Loads our layout template, settings its title and content.
*/
$template->assign_vars(array(
	'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
	'CONTENT'	=> $movieslist->output(),
	'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
	'ERROR'		=> (sizeof($error)) ? '<strong style="color:red">' . implode('<br />', $error) . '</strong>' : '',
));
/**
* Finally we can output our final page.
*/
page_header($lang['INDEX'] . ' - ' . $lang['MOVIES']);

$template->set_filename('index_body.html');

page_footer();