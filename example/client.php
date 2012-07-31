<?php

require_once '../StageBloc.php';

$clientId = '<YOUR CLIENT ID>';
$clientSecret = '<YOUR CLIENT SECRET>';
$redirectUri = '<YOUR REDIRECT URI>';

$client = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
$authUrl = $client->getAuthorizeUrl(array('scope' => 'non-expiring'));

?>

<a href="<?php echo $authUrl; ?>">Connect With StageBloc</a>