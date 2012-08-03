<?php

require_once '../StageBloc.php';

//$clientId = '<YOUR CLIENT ID>';
//$clientSecret = '<YOUR CLIENT SECRET>';
//$redirectUri = '<YOUR REDIRECT URI>';

$clientId = '809d6e0bd9a36f4f9482f52603bb081d';
$clientSecret = '465ab4e6db4e875620f50ca12950f102';
$redirectUri = 'http://localhost/oauth/example/client.php';

$stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);

if ( isset($_GET['code']) )
{
	try
	{
	    $accessToken = $stagebloc->accessToken($_GET['code']);
		var_dump($accessToken);
	}
	catch (Services_StageBloc_Invalid_Http_Response_Code_Exception $e)
	{
	    exit($e->getMessage());
	}
}
else
{
	$authUrl = $stagebloc->getAuthorizeUrl(array('scope' => 'non-expiring'));
?>
	<a href="<?php echo $authUrl; ?>">Connect With StageBloc</a>
<?php
}
?>