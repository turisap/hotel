<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-Jul-17
 * Time: 10:13 AM
 */

namespace App\Controllers;

use Facebook;
use \Core\Controller;

class Info
{

    public static function getUserInfo() {

        //print_r(dirname(__DIR__, 2) . '/Facebook/src/autoload.php');
        //require_once(dirname(__DIR__, 2) . '/Facebook/src/Facebook/autoload.php');

        $fb = new Facebook\Facebook([
            'app_id' => '321611971611782',
            'app_secret' => '7151d1bf1477b4c8e7cd09540d0e74ce',
            'default_graph_version' => 'v2.2'
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email']; // optional

        try {
            if (isset($_SESSION['facebook_access_token'])) {
                $accessToken = $_SESSION['facebook_access_token'];
            } else {
                $accessToken = $helper->getAccessToken();
            }
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        if (isset($accessToken)) {
            if (isset($_SESSION['facebook_access_token'])) {
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            } else {
                // getting short-lived access token
                $_SESSION['facebook_access_token'] = (string) $accessToken;
                // OAuth 2.0 client handler
                $oAuth2Client = $fb->getOAuth2Client();
                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
                $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
                // setting default access token to be used in script
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }
            // redirect the user back to the same page if it has "code" GET variable
            if (isset($_GET['code'])) {
                header('Location: ./');
            }
            // getting basic info about user
            try {
                $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
                $profile = $profile_request->getGraphNode()->asArray();
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                session_destroy();
                // redirecting user back to app login page
                header("Location: ./");
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            // printing $profile array on the screen which holds the basic info about user
            //print_r($profile);
            $_SESSION['facebook_first_name'] = $profile['first_name'];
            $_SESSION['facebook_last_name']  = $profile['last_name'];
            $_SESSION['facebook_email']      = $profile['email'];
            Controller::redirect('/rooms/prebookRoom');
            // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
        } else {
            // replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
            $loginUrl = $helper->getLoginUrl('localhost/info/get-user-info', $permissions);
            echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
        }

    }
}