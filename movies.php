<?php
if (!defined('IN_W2W'))
{
	exit;
}

// Initial var setup
$movies = $data = array();

if ($data = $cache->get('movies'))
{
    $data = json_decode($data, true);
}
else
{
	$scanned_directory = array_diff(scandir($movies_folder), array('..', '.', 'folder.jpg'));

	foreach ($scanned_directory as $key => $value) 
	{
		$filename = basename($movies_folder . '/' . $value . '/', '.mkv');
		$new_string = preg_replace("/(19|20)\d{2}/", '', $value);
		$new_string = slugify($new_string);
		$saved_xml = $filename . '.xml';
		if (!file_exists($movies_folder . '/' . $value . '/' . $saved_xml))
		{
			$xml = "http://www.omdbapi.com/?t=$new_string&y=&plot=short&r=xml";
			if (file_put_contents($movies_folder . '/' . $value . '/' . $saved_xml, fopen($xml, 'r')))
			{
				$error[] = 'Saved xml file from OMDBAPI for ' . $new_string;
			}
			else
			{
				$error[] = 'Failed saving xml file from OMDBAPI for ' . $new_string;
			}
		}
		if ($handle = opendir($movies_folder . '/' . $value)) {

			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'xml')
				{
					$movie = simplexml_load_file($movies_folder . '/' . $value . '/' . $file);
					$json = json_encode($movie);
					$array = json_decode($json, true);
					$banner = str_replace('.xml', '.banner.jpg', $file);
					$background = str_replace('.xml', '.background.jpg', $file);
					if (!file_exists($movies_folder . '/' . $value . '/' . $banner))
					{
						$movie_id = $array['id'];
						$fanart = curl("http://webservice.fanart.tv/v3/movies/$movie_id?api_key=b28b14e9be662e027cfbc7c3dd600405");
						$result = json_decode($fanart, true);
						if(isset($result['moviebanner']))
						{
							if (file_put_contents($movies_folder . '/' . $value . '/' . $banner, fopen($result['moviebanner'][0]['url'], 'r')))
							{
								$error[] = 'Saved banner from fanart.tv for ' . $array['title'];
							}
							else
							{
								$error[] = 'Failed saving banner from fanart.tv for ' . $array['title'];
							}
						}
						if (!isset($result['moviebanner']) && isset($result['moviebackground']))
						{
							if (file_put_contents($movies_folder . '/' . $value . '/' . $background, fopen($result['moviebackground'][0]['url'], 'r')))
							{
								$error[] = 'Saved background from fanart.tv for ' . $array['title'];
							}
							else
							{
								$error[] = 'Failed saving background from fanart.tv for ' . $array['title'];
							}
						}
						if (!isset($result['moviebanner']) && file_exists($movies_folder . '/' . $value . '/' . $background))
						{
							$rsr_org = imagecreatefromjpeg($movies_folder . '/' . $value . '/' . $background);
							$im = imagescale($rsr_org, 1000, 185,  IMG_BICUBIC_FIXED);
							$got_bg = true;
						}
						else
						{
							// Create the image
							$im = imagecreatetruecolor(1000, 185);
							$got_bg = false;
						}
							// Create some colors
							$white = imagecolorallocate($im, 255, 255, 255);
							$grey = imagecolorallocate($im, 128, 128, 128);
							$black = imagecolorallocate($im, 0, 0, 0);
							$text_color = imagecolorallocate($im, 233, 14, 91);
							//imagefilledrectangle($im, 0, 0, 399, 29, $white);

							// The text to draw
							$text = $movie->title;
							// Replace path by your own font path
							$font = 'movie.ttf';

							// Add some shadow to the text
							imagettftext($im, 72, 0, 19, 129, $grey, $font, $text);

							// Add the text
							imagettftext($im, 72, 0, 20, 128, $text_color, $font, $text);

							// Save the image
							imagejpeg($im, $movies_folder . '/' . $value . '/' . $banner);

							// Free up memory
							imagedestroy($im);
							if ($got_bg)
							{
								imagedestroy($rsr_org);
							}
							$error[] = 'Created banner for ' . $array['title'];
					}
					$dir_to_save = __DIR__ . '/images/';
					if (!is_dir($dir_to_save))
					{
						mkdir($dir_to_save);
					}
					if (!file_exists($dir_to_save . $banner))
					{
						$get_banner = file_get_contents($movies_folder . '/' . $value . '/' . $banner);
						file_put_contents($dir_to_save . $banner, $get_banner);
					}
					if (!isset($array['mpaa']))
					{
						$array['mpaa'] = 'Not Rated';
					}
					$moviemeter = file_get_contents("http://mickroz.nl/moviemeter.php?imdbid=" . $array['id']);
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
					$movies[$array['id']]['movieid'] = $array['id'];
					$movies[$array['id']]['title'] = $array['title'];
					$movies[$array['id']]['runtime'] = $array['runtime'];
					$movies[$array['id']]['year'] = $array['year'];
					$movies[$array['id']]['mpaa'] = $array['mpaa'];
					$movies[$array['id']]['plot'] = $plot;
					$movies[$array['id']]['genre'] = is_array($array['genre']) ? implode(",", $array['genre']) : $array['genre'];
					$movies[$array['id']]['banner'] = $banner;
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