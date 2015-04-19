<?php
if (!defined('IN_W2W'))
{
	exit;
}

include('includes/functions_movie.php');

// Initial var setup
$movies = $data = array();
$tag = "Movies";
$checkin = (isset($_GET['checkin'])) ? $_GET['checkin'] : '';
$getfanart = (isset($_GET['getfanart'])) ? $_GET['getfanart'] : '';

if ($checkin)
{
	if ($submit)
	{
		$message = $_POST['message'];
		$imdb_id = $_POST['imdb_id'];
		$trakt_checkin = trakt_movie_checkin($imdb_id, $message);
		$trakt_show_checkin = json_decode($trakt_checkin, true);
		
		if (!isset($trakt_show_checkin['expires_at']))
		{
			$movie_title = $trakt_show_checkin['movie']['title'];
			$error[] = sprintf($lang['TRAKT_CHECKIN'], $movie_title);
		}
		else
		{
			$error[] = $lang['TRAKT_ERROR'];
		}
	}
}

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
				if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'xml')
				{
					$search = array('.mkv', '.avi', '.mpeg', '.mp4');
					$filename = str_replace($search, '.xml', $file);
					// First we check if there is a xml file in cache
					if (!file_exists(CACHE_XML . '/' . $filename))
					{
						// If there is no xml file in cache, we try to grab it from movie folder
						if (file_exists($movies_folder . '/' . $value . '/' . $filename))
						{
							createXml($filename, $movies_folder . '/' . $value);
						}
						else
						{
							createXml($filename);
						}
					}
				
					if (file_exists(CACHE_XML . '/' . $filename))
					{
						$movie = array(
							'movieid' 	=> '',
							'title' 	=> '',
							'runtime' 	=> '',
							'year' 		=> '',
							'mpaa' 		=> '',
							'plot' 		=> '',
							'genre' 	=> '',
							'banner' 	=> '',
						);
						$movie = readXml(CACHE_XML . '/' . $filename);
						$movie_id = $movie['movieid'];
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
								createImage($movie['title'], $banner, $rsr_org, $im, $got_bg);
							}
						}
						else
						{
							$url = $movies_folder . '/' . $value . '/' . $banner;
							saveImage($url, $banner, $movie['title']);
						}
					
						$movies[$movie_id]['movieid'] = $movie['movieid'];
						$movies[$movie_id]['title'] = $movie['title'];
						$movies[$movie_id]['runtime'] = $movie['runtime'];
						$movies[$movie_id]['year'] = $movie['year'];
						$movies[$movie_id]['mpaa'] = $movie['mpaa'];
						$movies[$movie_id]['plot'] = $movie['plot'];
						$movies[$movie_id]['genre'] = $movie['genre'];
						$movies[$movie_id]['banner'] = $banner;

						if (empty($movie_id) || empty($movie['title']) || empty($movie['runtime']) || empty($movie['year']) || empty($movie['genre']) || empty($banner))
						{
							$log->debug($tag, sprintf($lang['DEBUG_DUMP'], json_encode($movies[$movie_id])));
						}
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

if ($getfanart)
{
	$banner = $data[$getfanart]['banner'];
	unlink(CACHE_IMAGES . '/' . $banner);
	$background = str_replace('.banner.jpg', '.background.jpg', $banner);
	$string = $data[$getfanart]['location'];
	$explode = explode( '/', $string );
	$location = str_replace('/' . $explode[3], '', $string);
	$image = getFanart('movies', $location, $explode[3], $data[$getfanart]['movieid'], $banner, $background);
	
	if ($image['grabbed'] == false)
	{
		$rsr_org = $image['rsr_org'];
		$im = $image['im'];
		$got_bg = $image['got_bg'];
		createImage($data[$getfanart]['title'], $banner, $rsr_org, $im, $got_bg);
	}
	header('Location: index.php?mode=movies');
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