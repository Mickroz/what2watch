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

function random_text( $type = 'alnum', $length = 8 )
{
	switch ( $type ) {
		case 'alnum':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'alpha':
			$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'hexdec':
			$pool = '0123456789abcdef';
			break;
		case 'numeric':
			$pool = '0123456789';
			break;
		case 'nozero':
			$pool = '123456789';
			break;
		case 'distinct':
			$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
		default:
			$pool = (string) $type;
			break;
	}


	$crypto_rand_secure = function ( $min, $max ) {
		$range = $max - $min;
		if ( $range < 0 ) return $min; // not so random...
		$log    = log( $range, 2 );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes
		$bits   = (int) $log + 1; // length in bits
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ( $rnd >= $range );
		return $min + $rnd;
	};

	$token = "";
	$max   = strlen( $pool );
	for ( $i = 0; $i < $length; $i++ ) {
		$token .= $pool[$crypto_rand_secure( 0, $max )];
	}
	return $token;
}

function generate_token($length = 20)
{
    return bin2hex(random_text($length));
}

function check_trakt_token()
{
	global $trakt_expires_in, $trakt_refresh_token, $log, $lang, $error, $result_token;
	
	if (time() >= $trakt_expires_in)
	{
		$check_trakt_token = getUrl("http://www.mickroz.nl/trakt.php?refresh=$trakt_refresh_token");

		$result_token = json_decode($check_trakt_token, true);
		if (empty($result_token))
		{
			return;
		}
		$data = file('config.php'); // reads an array of lines
		function refresh_config($data)
		{
			global $result_token;
			
			$time = time() + $result_token['expires_in'];
			
			if (stristr($data, '$trakt_token'))
			{
				return "\$trakt_token = '" . $result_token['access_token'] . "';\n";
			}
			if (stristr($data, '$trakt_expires_in'))
			{
				return "\$trakt_expires_in = '" . $time . "';\n";
			}
			if (stristr($data, '$trakt_refresh_token'))
			{
				return "\$trakt_refresh_token = '" . $result_token['refresh_token'] . "';\n";
			}
			return $data;
		}
		$data = array_map('refresh_config', $data);
		file_put_contents('config.php', implode('', $data));
		$log->info('trakt.tv', 'Updated trakt token, new date for refresh is ' . date('Y-m-d H:i:s', time() + $result_token['expires_in']));
	}
}

function getFanart($cat, $location, $name, $id, $banner, $background)
{
	global $log, $lang, $info, $error;
	$tag = 'getFanart';

	$cat_banner = ($cat == 'tv' ? 'tvbanner' : 'moviebanner');
	$cat_bg = ($cat == 'tv' ? 'showbackground' : 'moviebackground');
	$grabbed = false;
	$fanart = getUrl("http://webservice.fanart.tv/v3/$cat/$id?api_key=b28b14e9be662e027cfbc7c3dd600405", $tag);

	$result = json_decode($fanart, true);

	if(isset($result[$cat_banner]))
	{
		if (file_put_contents(CACHE_IMAGES . '/' . $banner, fopen($result[$cat_banner][0]['url'], 'r')))
		{
			$grabbed = true;
			$log->debug($tag, sprintf($lang['GRABBING_FANART'], $cat_banner . ' ' . $result[$cat_banner][0]['url']));
			$info[] = sprintf($lang['SAVED_FANART'], $cat_banner, $result['name']);
			$log->info($tag, sprintf($lang['SAVED_FANART'], $cat_banner, $result['name']));
		}
		else
		{
			$error[] = sprintf($lang['SAVED_FANART_FAILED'], $cat_banner, $result['name']);
			$log->error($tag, sprintf($lang['SAVED_FANART_FAILED'], $cat_banner, $result['name']));
		}
	}
	if (!isset($result[$cat_banner]) && isset($result[$cat_bg]))
	{
		if (file_put_contents(CACHE_IMAGES . '/' . $background, fopen($result[$cat_bg][0]['url'], 'r')))
		{
			$log->debug($tag, sprintf($lang['GRABBING_FANART'], $cat_bg . ' ' . $result[$cat_bg][0]['url']));
			$info[] = sprintf($lang['SAVED_FANART'], $cat_bg, $result['name']);
			$log->info($tag, sprintf($lang['SAVED_FANART'], $cat_bg, $result['name']));
		}
		else
		{
			$error[] = sprintf($lang['SAVED_FANART_FAILED'], $cat_bg, $result['name']);
			$log->error($tag, sprintf($lang['SAVED_FANART_FAILED'], $cat_bg, $result['name']));
		}
	}
	if (!isset($result[$cat_banner]) && file_exists(CACHE_IMAGES . '/' . $background))
	{
		$log->debug($tag, sprintf($lang['CREATED_FANART'], $cat_bg . ' ' . $location . '/' . $name . '/' . $background));
		$rsr_org = imagecreatefromjpeg(CACHE_IMAGES . '/' . $background);
		$im = imagescale($rsr_org, 1000, 185,  IMG_BICUBIC_FIXED);
		$got_bg = true;
	}
	else
	{
		// Create the image
		$rsr_org = '';
		$im = imagecreatetruecolor(1000, 185);
		$got_bg = false;
	}
	$array = array(
		'rsr_org'	=> $rsr_org,
		'im'		=> $im,
		'got_bg'	=> $got_bg,
		'grabbed'	=> $grabbed,
	);
	return $array;
}

function createImage($title, $banner, $rsr_org, $im, $got_bg)
{
	global $log, $lang, $info;
	
	$tag = 'createImage';
	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	//imagefilledrectangle($im, 0, 0, 399, 29, $white);

	// The text to draw
	$text = $title;
	
	// Set the enviroment variable for GD
	putenv('GDFONTPATH=' . realpath('.'));

	// Name the font to be used (note the lack of the .ttf extension)
	$font = 'movie';

	// Add some shadow to the text
	imagettftext($im, 72, 0, 19, 129, $grey, $font, $text);

	// Add the text
	imagettftext($im, 72, 0, 20, 128, $text_color, $font, $text);

	// Save the image
	imagejpeg($im, CACHE_IMAGES . '/' . $banner);

	// Free up memory
	imagedestroy($im);
	if ($got_bg)
	{
		imagedestroy($rsr_org);
	}
	$info[] = sprintf($lang['CREATED_BANNER'], $title);
	$log->info($tag, sprintf($lang['CREATED_BANNER'], $title));
}

function saveImage($url, $banner, $name)
{
	global $log, $lang;
	
	$tag = 'saveImage';
	
	$dir_to_save = CACHE_IMAGES;
	if (!is_dir($dir_to_save))
	{
		$log->debug($tag, 'Cannot find ' . $dir_to_save);
		mkdir($dir_to_save);
	}
	if (!file_exists($dir_to_save . '/' . $banner))
	{
		//$get_banner = file_get_contents($url);
		$get_banner = imagecreatefromjpeg($url);
		$save_banner = imagescale($get_banner, 1000, 185,  IMG_BICUBIC_FIXED);
		// Save the image
		imagejpeg($save_banner, CACHE_IMAGES . '/' . $banner);

		// Free up memory
		imagedestroy($get_banner);
		imagedestroy($save_banner);
		//file_put_contents($dir_to_save . '/' . $banner, $save_banner);
		$log->info($tag, sprintf($lang['SAVED_BANNER'], $name . ' (' . $dir_to_save . '/' . $banner . ')'));
	}
	return;
}

function getUrl($url, $tag='getUrl')
{
	global $log, $error, $sb_api;

	$log->debug($tag, "Opening URL " . str_replace($sb_api, 'xxx', $url));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'What2Watch');
	$data = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error($tag, curl_error($ch));
	}
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

function slugify($phrase)
{
    $result = strtolower($phrase);
	$result = str_replace("'", "-", $result);
	$result = str_replace(".", "-", $result);
    $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
    $result = trim(preg_replace("/[\s-]+/", " ", $result));
    $result = preg_replace("/\s/", "-", $result);
    
    return $result;
}

function get_slug($id)
{
	global $log, $lang, $error;
	
		$type = 'tvdb';
		$return = 'show';
		
	$log->debug('getSlug', sprintf($lang['GET_SLUG'], $id));
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-v2launch.trakt.tv/search?id_type=$type&id=$id");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"trakt-api-version: 2",
		"trakt-api-key: dfca522ce536a330d25737752dc8a26e2a5ac09e9067409669f3456db4089b7b"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		$error[] = curl_error($ch);
		$log->error('getSlug', curl_error($ch));
	}
	curl_close($ch);
	
	$result = json_decode($response, true);
	if (!empty($result))
	{
		$key = array_search('show', $result);
		return $result[$key][$return]['ids']['slug'];
	}
	else
	{
		return false;
	}
}

/*
* This code was created by Philippe MC
* https://github.com/xaccrocheur/
*/
function version_check()
{
	global $lang;
	$tag = 'versionCheck';
	$current_commits = getUrl("https://api.github.com/repos/Mickroz/what2watch/commits", $tag);

	if ($current_commits !== false)
	{
		$commits = json_decode($current_commits);
		exec('git rev-parse --verify HEAD 2> /dev/null', $git);
		$ref_commit = $git[0];
		$current_commit = $commits[0]->sha;
		$commit_message = $commits[0]->commit->message;
		
		$version['hash'] = substr($ref_commit, -7);
		
		if (!strcmp($current_commit, $ref_commit))
		{
			$version['style'] = ' style="color: #228822;"';
			$version['message'] = $lang['VERSION_UP_TO_DATE'];
		}
		else
		{
			$version['style'] = ' style="color: #BC2A4D;"';
			$version['message'] = sprintf($lang['VERSION_NOT_UP_TO_DATE'], $commit_message);
		}
	}
	else
	{
		$version['hash'] = '';
		$version['style'] = '';
		$version['message'] = $lang['VERSIONCHECK_FAIL'];
	}
	
	return $version;
}
function install()
{
	//install code
}

function update()
{
	//update code
}

function create_config_file_data($data)
{
	$config_data = "<?php\n";
	$config_data .= "// sickbeard should be with http:// and port\n";
	$config_data .= "// cache_life is caching time, in seconds\n";
	$config_data_array = array(
		'trakt_token'		=> $data['trakt_token'],
		'trakt_expires_in'	=> $data['trakt_expires_in'],
		'trakt_refresh_token'		=> $data['trakt_refresh_token'],
		'sickbeard'			=> $data['sickbeard'],
		'sb_api'			=> $data['sb_api'],
		'cache_life' 		=> $data['cache_life'],
		'sub_ext'			=> $data['sub_ext'],
		'movies_folder'		=> $data['movies_folder'],
		'web_username'		=> $data['web_username'],
		'web_password'		=> $data['web_password'],
		'ignore_words'		=> $data['ignore_words'],
		'skip_shows'		=> $data['skip_shows'],
		'skip_incomplete'	=> $data['skip_incomplete'],
		'skip_not_watched'	=> $data['skip_not_watched'],
		'template_path'		=> $data['template_path'],
		'language'			=> $data['language'],
		'ip_subnet'			=> $data['ip_subnet'],
		'debug'				=> $data['debug'],
		'config_version'	=> $data['config_version'],
		'random1'			=> $data['random1'],
		'random2'			=> $data['random2'],
	);

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}
	
	$config_data .= "\n@define('W2W_INSTALLED', true);\n";
	
	return $config_data;
}

function create_config_file()
{
	global $lang, $template, $log, $error, $msg_title;
	$data = get_submitted_data();
	$written = false;
	$error = array();
	$config_options = $data;
	$config_data = create_config_file_data($data);
	
	// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
	if (file_exists('config.php') && is_writable('config.php'))
	{
		// Assume it will work ... if nothing goes wrong below
		$written = true;

		if (!($fp = @fopen('config.php', 'w')))
		{
			// Something went wrong ...
			$written = false;
		}
		if (!(@fwrite($fp, $config_data)))
		{
			// Something went wrong ... 
			$written = false;
		}
		@fclose($fp);
		
		if ($written)
		{
			$chmod = @chmod('config.php', 0644);
			if (!$chmod)
			{
				$error[] = $lang['FAILED_CHMOD'];
				$log->warning('config', $lang['FAILED_CHMOD']);
			}
		}
	}
	if (isset($_POST['dldone']))
	{
		// Do a basic check to make sure that the file has been uploaded
		// Note that all we check is that the file has _something_ in it
		// We don't compare the contents exactly - if they can't upload
		// a single file correctly, it's likely they will have other problems....
		if (filesize('config.php') > 10)
		{
			$written = true;
		}
	}
	
	if (!$written)
	{
		$s_hidden_fields = '';
		foreach ($data as $config_key => $config_value)
		{
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $config_value . '" />';
		}
		// OK, so it didn't work let's try the alternatives

		if (isset($_POST['dlconfig']))
		{
			// They want a copy of the file to download, so send the relevant headers and dump out the data
			header("Content-Type: text/x-delimtext; name=\"config.php\"");
			header("Content-disposition: attachment; filename=config.php");
			echo $config_data;
			exit;
		}
		
		// The option to download the config file is always available, so output it here
		page_header($lang['INDEX'] . ' - ' . $lang['DL_CONFIG']);
		$template->set_filename('install_dlconfig.html');
		$template->assign_vars(array(
			'S_HIDDEN'				=> $s_hidden_fields,
		));
		page_footer();
	}
	else
	{
		$redirect_url = "index.php";
		meta_refresh(5, $redirect_url);
		msg_handler(sprintf($lang['CONFIG_WRITTEN_EXPLAIN'], '<a href="index.php">' . $lang['HERE'] . '</a>'), 'SUCCESS', 'success');
	}
}
/**
* Get submitted data
*/
function get_submitted_data()
{
	return array(
		'trakt_token'		=> $_POST['trakt_token'],
		'trakt_expires_in'	=> $_POST['trakt_expires_in'],
		'trakt_refresh_token'		=> $_POST['trakt_refresh_token'],
		'sickbeard'			=> $_POST['sickbeard'],
		'sb_api'			=> $_POST['sb_api'],
		'cache_life'		=> $_POST['cache_life'],
		'sub_ext'			=> $_POST['sub_ext'],
		'movies_folder'		=> $_POST['movies_folder'],
		'web_username'		=> $_POST['web_username'],
		'web_password'		=> $_POST['web_password'],
		'ignore_words'		=> $_POST['ignore_words'],
		'skip_shows'		=> $_POST['skip_shows'],
		'skip_incomplete'	=> isset($_POST['skip_incomplete']) ? 1 : 0,
		'skip_not_watched'	=> isset($_POST['skip_not_watched']) ? 1 : 0,
		'language'			=> $_POST['language'],
		'template_path'		=> $_POST['template_path'],
		'ip_subnet'			=> $_POST['ip_subnet'],
		'debug'				=> isset($_POST['debug']) ? 1 : 0,
		'config_version'	=> $_POST['config_version'],
		'random1'			=> generate_token(14),
		'random2'			=> generate_token(14),
	);
}

function set_lang($language)
{
	global $lang;
	$language_filename = 'language/' . $language . '/lang.php';
	
	if (!file_exists($language_filename))
	{
		if ($language == 'en')
		{
			// The user's selected language is missing and the default language is missing
			$language_filename = str_replace('language/en', 'language/' . $language, $language_filename);
			trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
		}
		else
		{
			// Fall back to the English Language
			$this->set_lang('en');
		}
		return;
	}
	$include_result = include $language_filename;
	if ($include_result === false)
	{
		trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
	}
	
	// Include active plugins language files
	if ($active_plugins = @file_get_contents("plugins/active.json"))
	{
		$json_a = json_decode($active_plugins, true);
		
		foreach ($json_a as $key => $value)
		{
			$file = 'language/' . $language . '/' . $key . '.php';
			if(file_exists($file))
			{
				include $file;
			}
		}
	}
}

function meta_refresh($time, $url)
{
	global $template;
	define('META_REFRESH', true);

	$url = str_replace('&', '&amp;', $url);

	// For XHTML compatibility we change back & to &amp;
	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="' . $time . '; url=' . $url . '" />')
	);

	return $url;
}

function msg_handler($msg_text, $msg_title = '', $type = '')
{
	global $template_path, $template, $lang;
	global $error;

	if (empty($type))
	{
		$type = 'info';
	}
	$msg_text = (!empty($lang[$msg_text])) ? $lang[$msg_text] : $msg_text;
	$msg_title = (!isset($msg_title)) ? $lang['INFORMATION'] : ((!empty($lang[$msg_title])) ? $lang[$msg_title] : $msg_title);
	
	$msg_handler = new template();
	$msg_handler->set_template();
	$msg_handler->set_filename('message_body.html');

	$msg_handler->assign_vars(array(
		'TYPE'				=> $type,
		'MESSAGE_TITLE'		=> $msg_title,
		'MESSAGE_TEXT'		=> $msg_text)
	);

	if (defined('META_REFRESH'))
	{
		$template->assign_vars(array(
			'CONTENT'	=> $msg_handler->output(),
		));

		page_header($msg_title);
	
		$template->set_filename('index_body.html');
		
		page_footer();
	}
	else
	{
		return $msg_handler->output();
	}
}
/**
* Generate page header
*/
function page_header($page_title = '')
{
	global $lang, $template, $template_path, $error, $version, $success, $info, $warning, $trakt;
	
	$notifier = file_get_contents('styles/' . $template_path . '/message_body.html');

	$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');
	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'SHOWS_ACTIVE'	=> ($mode == 'shows' ? ' class="active"' : ''),
		'MOVIES_ACTIVE'	=> ($mode == 'movies' ? ' class="active"' : ''),
		'LOG_ACTIVE'	=> ($mode == 'viewlog' ? ' class="active"' : ''),
		'CONFIG_ACTIVE'	=> (($mode == 'config') ? ' class="active"' : ''),
		'PLUGINS_ACTIVE'	=> (($mode == 'plugins') ? ' class="active"' : ''),
		'INFO_ACTIVE'	=> ($mode == 'info' ? ' class="active"' : ''),
		'DROPDOWN_ACTIVE'	=> (($mode == 'config' || $mode == 'plugins' || $mode == 'info') ? ' active' : ''),
		'STYLESHEET_LINK'	=> 'styles/' . $template_path . '/style.css',
		'TEMPLATE_PATH'	=> 'styles/' . $template_path,
		'PAGE_TITLE'	=> $page_title,
		'ERROR'		=> (sizeof($error)) ? strtr($notifier, array('{TYPE}' => 'danger', '{MESSAGE_TITLE}' => $lang['ERROR'], '{MESSAGE_TEXT}' => implode('<br />', $error))) : '',
		'SUCCESS'	=> (sizeof($success)) ? strtr($notifier, array('{TYPE}' => 'success', '{MESSAGE_TITLE}' => $lang['SUCCESS'], '{MESSAGE_TEXT}' => implode('<br />', $success))) : '',
		'INFORMATION'		=> (sizeof($info)) ? strtr($notifier, array('{TYPE}' => 'info', '{MESSAGE_TITLE}' => $lang['INFORMATION'], '{MESSAGE_TEXT}' => implode('<br />', $info))) : '',
		'WARNING'	=> (sizeof($warning)) ? strtr($notifier, array('{TYPE}' => 'warning', '{MESSAGE_TITLE}' => $lang['WARNING'], '{MESSAGE_TEXT}' => implode('<br />', $warning))) : '',
		'VERSION'	=> '<p' . $version['style'] . '><strong>' . $version['message'] . '</strong></p>',
		'TRAKT'		=> (isset($trakt)) ? $trakt : '',
	));
}

/**
* Generate page footer
*/
function page_footer()
{
	global $lang, $template;
	
	echo $template->output();
	exit_handler();
}

function exit_handler()
{
	exit;
}