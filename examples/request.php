<?php

require_once '../StageBloc.php';

$clientId = '<YOUR CLIENT ID>';
$clientSecret = '<YOUR CLIENT SECRET>';
$redirectUri = '<YOUR REDIRECT URI>';
$accessToken = '<YOUR ACCESS TOKEN>'; // This can be received using authentication.php

// If sent here from authentication.php, we can grab the access token from there
if ( ! empty($_GET) && isset($_GET['accessToken']) )
{
	$accessToken = $_GET['accessToken'];
}

$stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
$stagebloc->setAccessToken($accessToken);
$stagebloc->setResponseFormat('xml'); // XML is default, JSON is also accepted

$items = $stagebloc->get('statuses/list');

// If the response format is XML, it might be easier to understand the output by viewing the page source
echo $items;

?>