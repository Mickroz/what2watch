<?php

// CHANGE THIS! request api key at http://www.moviemeter.nl/site/registerclient/
$api_key = '1234567890';

// CHANGE THIS! path to writable directory
$path = 'moviemeter/';


$filmId = intval($_GET['filmId']);

if ($filmId == 0)
{
	exit;
}

$path_to_file = $path . 'mome_' . $filmId . '.jpg';

if (is_file($path_to_file))
{
	// use image from local cache
	$contents = file_get_contents($path_to_file);
}
else
{
	require("lib/xmlrpc.inc");
	require("lib/xmlrpcs.inc");
	require("lib/xmlrpc_wrappers.inc");

	// start client
	$client = new xmlrpc_client("http://www.moviemeter.nl/ws");
	$client->return_type = 'phpvals';


	// retrieve session key
	$message = new xmlrpcmsg("api.startSession", array(new xmlrpcval($api_key, "string")));
	$resp = $client->send($message);
	$session_info = $resp->value();
  	$session_key = $session_info['session_key'];

	// retrieve image
	$message = new xmlrpcmsg("film.retrieveImage", array(new xmlrpcval($session_key, "string"), new xmlrpcval($filmId, "int")));
	$resp = $client->send($message);
	$image = $resp->value();
	$contents = base64_decode($image['image']['base64_encoded_contents']);

	// cache this image
	$handle = fopen($path_to_file, 'w+');
	fwrite($handle, $contents);
	flose($handle);
}

// output file
echo $contents;
?>