<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>phpTT &bull; php Tiny Thoughts</title>
	
	<link rel="stylesheet" href="highlight-github.css">
</head>
<body>	
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
$string = '[code]<?php
$code = "coding is fun";
?>[/code] [b]Hello world![/b] [url=http://www.mickroz.nl/]coding is awesome[/url]
[url]http://www.mickroz.nl[/url]
:D';
echo $string;
$string = parse_bbcode($string);
$string = parse_smilies($string);

echo $string;
?>
<script src="highlight.pack.js"></script>
<script>
		hljs.initHighlightingOnLoad();
	</script>
</body>
</html>
<?php
/**
* Parse BBCode
*/
function parse_bbcode($str)
{
	// Convert all special HTML characters into entities to display literally
	$str = htmlentities($str);
	// The array of regex patterns to look for
	$format_search = array(
		'#\[b\](.*?)\[/b\]#is', // Bold ([b]text[/b]
		'#\[i\](.*?)\[/i\]#is', // Italics ([i]text[/i]
		'#\[u\](.*?)\[/u\]#is', // Underline ([u]text[/u])
		'#\[s\](.*?)\[/s\]#is', // Strikethrough ([s]text[/s])
		'#\[quote\](.*?)\[/quote\]#is', // Quote ([quote]text[/quote])
		'#\[c\](.*?)\[/c\]#is', // Monospaced code [code]text[/code])
		'#\[code\](.*?)\[/code\]#is', // Monospaced code [code]text[/code])
		'#\[size=([1-9]|1[0-9]|20)\](.*?)\[/size\]#is', // Font size 1-20px [size=20]text[/size])
		'#\[color=\#?([A-F0-9]{3}|[A-F0-9]{6})\](.*?)\[/color\]#is', // Font color ([color=#00F]text[/color])
		//'#\[url(=(.*))?\](?(1)((?s).*(?-s))|(.*))\[/url\]#is',
		'#\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]#i', // Hyperlink with descriptive text ([url=http://url]text[/url])
		'#\[url\]((?:ftp|https?)://.*?)\[/url\]#i', // Hyperlink ([url]http://url[/url])
		'#\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]#i' // Image ([img]http://url_to_image[/img])
	);
	// The matching array of strings to replace matches with
	$format_replace = array(
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<span style="text-decoration: underline;">$1</span>',
		'<span style="text-decoration: line-through;">$1</span>',
		'<blockquote>$1</blockquote>',
		'<code>$1</code>',
		'<pre><code>$1</code></'.'pre>',
		'<span style="font-size: $1px;">$2</span>',
		'<span style="color: #$1;">$2</span>',
		'<a href="$1">$2</a>',
		'<a href="$1">$1</a>',
		'<img src="$1" alt="" />'
	);
	// Perform the actual conversion
	$str = preg_replace($format_search, $format_replace, $str);
	// Convert line breaks in the <br /> tag
	$str = str_replace(array("\n", "\r"), array('<br />', "\n"), $str);

	return $str;
}
/**
* Parse Smilies
*/
function parse_smilies($str)
{
	$match = $replace = array();
	
	# -- Smilies
	$smilies = array(
		array('code' => ':D', 'smiley_url' => 'icon_e_biggrin.gif', 'emotion' => '{L_SMILIES_VERY_HAPPY}'),
		array('code' => ':-D', 'smiley_url' => 'icon_e_biggrin.gif', 'emotion' => '{L_SMILIES_VERY_HAPPY}'),
		array('code' => ':)', 'smiley_url' => 'icon_e_smile.gif', 'emotion' => '{L_SMILIES_SMILE}'),
		array('code' => ':-)', 'smiley_url' => 'icon_e_smile.gif', 'emotion' => '{L_SMILIES_SMILE}'),
		array('code' => ';)', 'smiley_url' => 'icon_e_wink.gif', 'emotion' => '{L_SMILIES_WINK}'),
		array('code' => ';-)', 'smiley_url' => 'icon_e_wink.gif', 'emotion' => '{L_SMILIES_WINK}'),
		array('code' => ':(', 'smiley_url' => 'icon_e_sad.gif', 'emotion' => '{L_SMILIES_SAD}'),
		array('code' => ':-(', 'smiley_url' => 'icon_e_sad.gif', 'emotion' => '{L_SMILIES_SAD}'),
		array('code' => ':o', 'smiley_url' => 'icon_e_surprised.gif', 'emotion' => '{L_SMILIES_SURPRISED}'),
		array('code' => ':-o', 'smiley_url' => 'icon_e_surprised.gif', 'emotion' => '{L_SMILIES_SURPRISED}'),
		array('code' => ':?', 'smiley_url' => 'icon_e_confused.gif', 'emotion' => '{L_SMILIES_CONFUSED}'),
		array('code' => ':-?', 'smiley_url' => 'icon_e_confused.gif', 'emotion' => '{L_SMILIES_CONFUSED}'),
		array('code' => '8-)', 'smiley_url' => 'icon_cool.gif', 'emotion' => '{L_SMILIES_COOL}'),
		array('code' => ':x', 'smiley_url' => 'icon_mad.gif', 'emotion' => '{L_SMILIES_MAD}'),
		array('code' => ':-x', 'smiley_url' => 'icon_mad.gif', 'emotion' => '{L_SMILIES_MAD}'),
		array('code' => ':P', 'smiley_url' => 'icon_razz.gif', 'emotion' => '{L_SMILIES_RAZZ}'),
		array('code' => ':-P', 'smiley_url' => 'icon_razz.gif', 'emotion' => '{L_SMILIES_RAZZ}'),
		array('code' => ':|', 'smiley_url' => 'icon_neutral.gif', 'emotion' => '{L_SMILIES_NEUTRAL}'),
		array('code' => ':-|', 'smiley_url' => 'icon_neutral.gif', 'emotion' => '{L_SMILIES_NEUTRAL}')
	);
	
	foreach($smilies as $key => $value)
	{
		$match[$key] = $value['code'];
		$replace[$key] = '<img src="{SMILIES_PATH}/' . $value['smiley_url'] . '" alt="' . $value['code'] . '" title="' . $value['emotion'] . '" />';
	}
	return str_replace($match, $replace, $str);
}
?>