<?php
if(isset($_GET['referer']))
{

	$fp = fopen('referer.php', 'w');
	$data = "<?php\n";
	$data .= "\$referer = '" . $_GET['referer'] . "';\n";
	$data .= "?>\n";
    fwrite($fp, $data);
    fclose($fp);
	
	$url = "https://trakt.tv/oauth/authorize";
	// CHANGE THIS CLIENT ID AND URL TO YOUR OWN
	$params = array(
		"response_type" => "code",
		"client_id" => "1234567890",
		"redirect_uri" => "http://www.link.to/trakt.php"
		);
 
	$request_to = $url . '?' . http_build_query($params);
 
	header("Location: " . $request_to);
}

if(isset($_GET['refresh']))
{
	$refresh = $_GET['refresh'];
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/oauth/token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_POST, TRUE);
	// CHANGE THIS CLIENT ID AND CLIENT SECRET TO YOUR OWN
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		\"refresh_token\": \"$refresh\",
		\"client_id\": \"1234567890\",
		\"client_secret\": \"1234567890\",
		\"redirect_uri\": \"urn:ietf:wg:oauth:2.0:oob\",
		\"grant_type\": \"refresh_token\"
	}");

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		echo curl_error($ch);
	}
	curl_close($ch);
	
	echo $response;
}

if(isset($_GET['code']))
{
	// try to get an access token
	$code = $_GET['code'];
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.trakt.tv/oauth/token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_POST, TRUE);
	// CHANGE THIS CLIENT ID AND CLIENT SECRET AND URL TO YOUR OWN
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{
  	\"code\": \"$code\",
  	\"client_id\": \"\",
  	\"client_secret\": \"\",
  	\"redirect_uri\": \"http://www.link.to/trakt.php\",
  	\"grant_type\": \"authorization_code\"
	}");

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  	"Content-Type: application/json"
	));

	$response = curl_exec($ch);
	if(curl_errno($ch))
	{
		echo curl_error($ch);
		exit;
	}
	curl_close($ch);
	
	$result = json_decode($response, true);

	include('referer.php');
	$url = $referer;
 
	$params = array(
		"access_token" => $result['access_token'],
		"expires_in" => $result['expires_in'],
		"refresh_token" => $result['refresh_token']
	);
 
	$request_to = $url . '?' . http_build_query($params);
	header("Location: " . $request_to);
}