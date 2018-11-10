<?php

namespace SocialiteProviders\Upwork;

use Illuminate\Support\Facades\Log;
use SocialiteProviders\Manager\OAuth1\AbstractProvider;
use SocialiteProviders\Manager\OAuth1\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'UPWORK';

    /**
     * {@inheritDoc}
     */
    public function user()
    {
    	dump('USER started 3');
        if (! $this->hasNecessaryVerifier()) {
            throw new \InvalidArgumentException("Invalid request. Missing OAuth verifier.");
        }

		$token = $this->getToken();
	    dump('Token', $token);
		$access_token = $token['tokenCredentials']->getIdentifier();
	    $access_secret = $token['tokenCredentials']->getSecret();
	    $oauth_token = request()->query('oauth_token');
	    $oauth_verifier = request()->query('oauth_verifier');
	    $requestToken = null;
	    $requestSecret = null;

	    //dd(session());
	    $config = new \Upwork\API\Config(
		    array(
			    'consumerKey'       => env('UPWORK_KEY'),  // SETUP YOUR CONSUMER KEY
			    'consumerSecret'    => env('UPWORK_SECRET'),                  // SETUP YOUR KEY SECRET
			    'accessToken'       => $access_token,       // got access token
			    'accessSecret'      => $access_secret,      // got access secret
			    'requestToken'      => null,      // got request token
			    'requestSecret'     => null,     // got request secret
			    'verifier'          => $oauth_verifier,         // got oauth verifier after authorization
			    'mode'              => 'web',                           // can be 'nonweb' for console apps (default),
			    // and 'web' for web-based apps
				//	'debug' => true, // enables debug mode. Note that enabling debug in web-based applications can block redirects
				//	'authType' => 'MyOAuth' // your own authentication type, see AuthTypes directory
		    )
	    );

	    dump('Config', $config);
	    $client = new \Upwork\API\Client($config);

	    if (empty($_SESSION['request_token']) && empty($_SESSION['access_token'])) {

	    	// we need to get and save the request token. It will be used again
		    // after the redirect from the Upwork site
		    $requestTokenInfo = $client->getRequestToken();

		    $_SESSION['request_token']  = $requestTokenInfo['oauth_token'];
		    $_SESSION['request_secret'] = $requestTokenInfo['oauth_token_secret'];
		    dump($_SESSION['request_token'], $_SESSION['request_secret']);
		    $client_auth = $client->auth();
		    dump('client->auth()', $client_auth);
		    $_SESSION['access_token'] = $client_auth['access_token'];
		    $_SESSION['access_secret'] = $client_auth['access_secret'];
	    } elseif (empty($_SESSION['access_token'])) {
		    dump('access token is empty', $_SESSION['access_token']);
		    // the callback request should be pointed to this script as well as
		    // the request access token after the callback
		    $accessTokenInfo = $client->auth();

		    $_SESSION['access_token']   = $accessTokenInfo['access_token'];
		    $_SESSION['access_secret']  = $accessTokenInfo['access_secret'];
	    }

		// $accessTokenInfo has the following structure
		// array('access_token' => ..., 'access_secret' => ...);
		// keeps the access token in a secure place

	    // if authenticated
	    dump('Before access_token');
	    if ($_SESSION['access_token']) {
		    dump('Inside access_token: ', $_SESSION['access_token']);
		    // clean up session data
		    unset($_SESSION['request_token']);
		    unset($_SESSION['request_secret']);

		    // gets info of the authenticated user
		    $auth = new \Upwork\API\Routers\Auth($client);
		    $info = $auth->getUserInfo();
			dump('info', $info);
		    //print_r($info);
	    }

	    //dump('$this', $this);

	    //dump('$this->server', $this->server);

	    //dump('$this->getToken()', $this->getToken());

        //$user = $this->server->getUserDetails($token = $this->getToken());
	    //$user = $info;
	    return $info;
	    dump('user', $user);
/*
        return (new User())->setRaw($user->extra)->map([
            'id'       => $user->id,
            'nickname' => $user->nickname,
            'name'     => $user->name,
            'email'    => $user->email,
            'avatar'   => $user->avatar,
        ])->setToken($token->getIdentifier(), $token->getSecret());
*/
    }
}
