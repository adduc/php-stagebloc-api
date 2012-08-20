<?php

require_once '../StageBloc.php';
require_once 'info.php';

// If sent here from authentication.php, we can grab the access token from there
// Note: You should copy this access token into the info.php file for further testing once you receive it
if ( ! empty($_GET) && isset($_GET['accessToken']) )
{
	$accessToken = $_GET['accessToken'];
}

// Setup our StageBloc OAuth object
$stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
$stagebloc->setAccessToken($accessToken);
$stagebloc->setResponseFormat('json'); // XML is default, JSON is also accepted

// Post a test status update
// Warning: This will update your StageBloc account with test data! Comment this out if you don't want that to happen!
$postData = array(
	'text' => 'Status update from StageBloc\'s PHP API wrapper!'
);
$test = $stagebloc->post('statuses/edit', $postData);

// Get the statuses for this account
$items = $stagebloc->get('statuses/list');

echo '<h2>Statuses</h2>';
// If the response format is XML, it might be easier to understand the output by viewing the page source
echo $items;

?>