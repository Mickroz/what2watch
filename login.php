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

if (isset($_POST['login']))
{
	if ($_POST['username'] == $web_username && $_POST['password'] == $web_password)
	{
		//IF USERNAME AND PASSWORD ARE CORRECT SET THE LOGIN SESSION
		$cookiedata = $random2 . ':' . $hash;
		setcookie($random1 . '_l', $cookiedata, time() + 86400);
		header("Location: $_SERVER[PHP_SELF]");
	}
	else
	{
		$error[] = array('type' => 'danger', 'msg' => 'Username or Password incorrect!');
	}
}

// show login box
page_header();

$template->set_filename('login_body.html');

page_footer();