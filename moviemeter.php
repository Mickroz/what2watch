<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require("lib/xmlrpc.inc");
require("lib/xmlrpcs.inc");
require("lib/xmlrpc_wrappers.inc");
// CHANGE THIS API KEY TO YOUR OWN
$api_key = '1234567890';
$imdbid = (isset($_GET['imdbid']) ? $_GET['imdbid'] : '');

if ($imdbid)
{
	// start client
	$client = new xmlrpc_client("http://www.moviemeter.nl/ws");
	$client->return_type = 'phpvals';

	/*
	*start session and retrieve sessionkey
	*/
	$message = new xmlrpcmsg("api.startSession", array(new xmlrpcval($api_key, "string")));
	$resp = $client->send($message);

	if ($resp->faultCode())
	{
		die('error: '. $resp->faultString());
	}
	else
	{
		$session_info = $resp->value();
		$session_key = $session_info['session_key'];
	}



	/*
	* search for movie
	*/
	$message = new xmlrpcmsg("film.retrieveByImdb", array(new xmlrpcval($session_key, "string"), new xmlrpcval($imdbid, "string")));
	$resp = $client->send($message);

	if ($resp->faultCode())
	{
		die('error: '. $resp->faultString());
	}
	else
	{
		$results = $resp->value();

		$message = new xmlrpcmsg("film.retrieveDetails", array(new xmlrpcval($session_key, "string"), new xmlrpcval($results, "int")));
		$resp = $client->send($message);
		if ($resp->faultCode())
		{
			die('error: '. $resp->faultString());
		}
		else
		{
			$results = $resp->value();
			echo $results['plot'];
		}
	}

	$message = new xmlrpcmsg("api.closeSession", array(new xmlrpcval($session_key, "string")));
	$resp = $client->send($message);
	
	if ($resp->faultCode())
	{
		die('error: '. $resp->faultString());
	}
	else
	{
		$results = $resp->value();
	}
	if (isset($results))
	{
		exit();
	}
}
else
{
	exit;
}