<?php

require_once '../StageBloc.php';

$clientId = '<YOUR CLIENT ID>';
$clientSecret = '<YOUR CLIENT SECRET>';
$redirectUri = '<YOUR REDIRECT URI>';

$stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
$stagebloc->setAccessToken('bd13f86dd13e1c5be2b439a3bd36a37a0238f1e0');
$stagebloc->setResponseFormat('xml'); // XML is default, JSON is also accepted

$tracks = $stagebloc->get('audio/list');

var_dump($tracks);

?>