<?php

require_once '../StageBloc.php';
require_once 'info.php';

$stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);

if ( isset($_GET['code']) )
{
	try
	{
	    $accessToken = $stagebloc->accessToken($_GET['code']);	
		echo 'Successfully authenticated! Your access token is ' . $accessToken['access_token'] . '<br/>' .
				'<a href="request.php?accessToken=' . $accessToken['access_token'] . '">Try an example request</a>';
	}
	catch (Services_StageBloc_Invalid_Http_Response_Code_Exception $e)
	{
	    exit($e->getMessage());
	}
}
else
{
	$authUrl = $stagebloc->getAuthorizeUrl(array('scope' => 'non-expiring'));
	echo '<a href="' . $authUrl . '">Connect With StageBloc</a>';
}
?>