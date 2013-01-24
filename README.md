# StageBloc API PHP Wrapper

## Introduction

A wrapper for the StageBloc API written in PHP with support for authentication using [OAuth 2.0](http://oauth.net/2/).

## Getting Started

To be able to test your application, you'll first need to [sign up for a StageBloc account](http://www.stagebloc.com/signup). If you've already done that, you'll need to [register your application](http://stagebloc.com/account/admin/management/developers/) and receive a client ID and secret.

## Examples

The wrapper includes convenient methods used to perform HTTP requests on behalf of the authenticated user.

Before being able to access any data related to an account, you'll first need to authenticate.

### Authentication

The authentication flow is explained below. There is also an example in `examples/authentication.php`.

1. The first step is to create a StageBloc object.

        $clientId = '<YOUR CLIENT ID>';
        $clientSecret = '<YOUR CLIENT SECRET>';
        $redirectUri = '<YOUR REDIRECT URI>';
        $stagebloc = new Services_StageBloc($clientId, $clientSecret, $redirectUri);
   
2. Then, you'll need to get the authorization URL.

        $authUrl = $stagebloc->getAuthorizeUrl(array('scope' => 'non-expiring'));
        <a href="<?php echo $authUrl; ?>">Connect With StageBloc</a>
       
   During this step, the user will be redirected to StageBloc's authentication page.
       
3. If the authentication is successful, the user will be redirected back to your application's redirect URL. You can then receive an access code.

        try {
           $accessToken = $stagebloc->accessToken($_GET['code']);
           var_dump($accessToken);
        } catch (Services_StageBloc_Invalid_Http_Response_Code_Exception $e) {
	       exit($e->getMessage());
        }
      	 
   The access token will allow you to use the StageBloc API to perform requests for the user.
   
### Requests

You can see how to setup requests in `examples/request.php`. Documentation for the StageBlocAPI itself can be found on [GitHub](https://github.com/stagebloc/docs/blob/master/api.md) or [StageBloc's develop page](http://stagebloc.com/developers/api/).

## Feedback And Questions

Found a bug or missing a feature? Don't hesitate to create a new issue here on GitHub.
