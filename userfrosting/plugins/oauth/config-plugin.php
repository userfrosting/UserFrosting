<?php

namespace oauth;

//die("Loading datatable");
class oauth {

    protected  $_oauthuser;     
    protected  $_provider;     

    public function setProvider($provider, $parms)
    {
        $var_pclass = "\League\OAuth2\Client\Provider\\".$provider;
        $this->provider[strtoupper($provider)] = new $$var_pclass($parms);
    }
    public static function init(&$app) {

//echo("This is the plugin config script");
        $this->_app=$app;

        $par_url=$this->_app->site->uri['public'];
        $this->setProvider('LinkedIn',[
            'clientId' => '78vtzpuzyf3njc',
            'clientSecret' => 'iNb4AGkxFSSClO1j',
            'fields' => [
                'id', 'email-address', 'first-name', 'last-name', 'headline',
                'location', 'industry', 'picture-url', 'public-profile-url',
            ],
            'redirectUri' => $par_url.'?oauthlogin=linkedin',
            'scopes' => ['r_basicprofile', 'r_emailaddress']]);
        
        $app('account.controller.hook', 
            function ($route, $provider, $url) use ($app){
                $this->oauthLogin($provider,$url);
            }, 
        1);  
//echo("This is the plugin config script");        
        }

    public function echobr($par_str) {
        echo("<br>$par_str<br>");
    }

    public function echoarr($par_arr, $par_comment = 'none') {
        if ($par_comment != 'none')
            echobr($par_comment);
        echo "<pre>";
        print_r($par_arr);
        echo "</pre>";
    }
    public function oauthLogin($par_provider,$par_url='') {
        $userDetails=false;

        if($par_url=='')
        {
            $par_url=$this->_app->site->uri['public'];
        }
        switch(strtoupper($par_provider))
        {
            case "LINKEDIN":
//            'redirectUri' => 'http://sniperecruit.localhost?oauthlogin=linkedin',
        $provider = new \League\OAuth2\Client\Provider\LinkedIn([
            'clientId' => '78vtzpuzyf3njc',
            'clientSecret' => 'iNb4AGkxFSSClO1j',
            'redirectUri' => $par_url.'?oauthlogin=linkedin',
            'scopes' => ['r_basicprofile', 'r_emailaddress'],
        ]);
        }
        $var_getarr = $this->_app->request->get();
        if (!isset($var_getarr['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->state;
            header('Location: ' . $authUrl);
            exit;

// Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($var_getarr['state']) || ($var_getarr['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $var_getarr['code']
            ]);

            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the user's details
                $userDetails = $provider->getUserDetails($token);

            } catch (Exception $e) {

                // Failed to get user details
                exit('Oh dear...');
            }

            // Use this to interact with an API on the users behalf
//            echo "Line 70" . $token->accessToken;
            $_SESSION['oauth_token']=$token;
            // Use this to get a new access token if the old one expires
//            echo "Line 73" . $token->refreshToken;

// Database insert here 
// To be completed this code below is for Readbean            
            
//$_SESSION["userfrosting"]["user"] = UserLoader::fetch($data['user_name'], 'user_name');
//$this->_app->user = $_SESSION["userfrosting"]["user"];
//$this->_app->user->login()            
            // Unix timestamp of when the token will expire, and need refreshing
//            echo "Line 76" . $token->expires;
//            $userDetails->token=$token;
            
//            $var_ufuser  = $this->_app->_R->find( 'ufuseroauth', ' uid ="'.$userDetails->uid.'"');
//            if(count($var_ufuser)==0)
//            {
//                $var_ufuser = $this->_app->_R->dispense("ufuseroauth");
//                $var_ufuser->uid = $userDetails->uid;
//                $var_ufuser->email = $userDetails->email;
//                $var_ufuser->first_name = $userDetails->firstName;
//                $var_ufuser->last_name = $userDetails->lastName;
//                $var_ufuser->picture_url = $userDetails->imageUrl;
//                $var_ufuser->oauth_details = serialize($userDetails);
//                $id = $this->_app->_R->store( $var_ufuser );
//        if (!$this->_app->user->isGuest()) {
////print_r($this->_app->user);  
//echo("Line 96 user id is ".$app->user->id);
//        }                
//            }
//            else
//            {
//print_r($this->_app->user);            
//echo("<br>Line 96 user id is ".$app->user->id);
//                
//            }
        }
            return $userDetails;
    }

}