<?php

require_once '../StageBloc.php';

$clientId = 'abb950fc6aa21ce491549a96ce6e9295';
$clientSecret = '55ff5d5aa479744c76681427086654b4';
$redirectUri = 'http://www.stagebloc.local/blank_canvas.php';

$client = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
$authUrl = $client->getAuthorizeUrl(array('scope' => 'non-expiring'));

?>

<a href="<?php echo $authUrl; ?>">Connect With StageBloc</a>