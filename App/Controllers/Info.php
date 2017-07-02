<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-Jul-17
 * Time: 10:13 AM
 */

namespace App\Controllers;


use Facebook;


class Info
{

    public function __construct(){
        $this->fb = new Facebook\Facebook([
            'app_id' => '321611971611782',
            'app_secret' => '7151d1bf1477b4c8e7cd09540d0e74ce',
            'default_graph_version' => 'v2.2'
        ]);
    }

    public function getUserInfo() {

        $helper = $this->fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // There was an error communicating with Graph
            echo $e->getMessage();
            exit;
        }

        if (isset($accessToken)) {
            // User authenticated your app!
            // Save the access token to a session and redirect
            $_SESSION['facebook_access_token'] = (string) $accessToken;
            // Log them into your web framework here . . .

            try {
                // Returns a `Facebook\FacebookResponse` object
                $response = $this->fb->get('/me?fields=email,first_name,last_name', $accessToken);
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $user = $response->getGraphUser();
            $_SESSION['facebook_first_name'] = $user['first_name'] ?? null;
            $_SESSION['facebook_last_name']  = $user['last_name'] ?? null;
            $_SESSION['facebook_email']      = $user['email'] ?? null;
            $checkin  = $_GET['checkin'] ?? false;
            $checkout = $_GET['checkout'] ?? false;
            $room_id  = $_GET['room_id']  ?? false;
            header('Location: http://' . $_SERVER['HTTP_HOST'] . "/rooms/prebook-room-action?id={$room_id}&checkin={$checkin}&checkout={$checkout}", true, 303);
            exit;

        } elseif ($helper->getError()) {
            // The user denied the request
            // You could log this data . . .
            var_dump($helper->getError());
            var_dump($helper->getErrorCode());
            var_dump($helper->getErrorReason());
            var_dump($helper->getErrorDescription());
            // You could display a message to the user
            // being all like, "What? You don't like me?"
            exit;
        }
        return false;
    }

    // creates a url for login (along with keeping room id and dates as query string for redirect in the method above)
    public function getLoginUrl($room_id, $checkin, $checkout){

        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = ['public_profile'];
        $callback    = "http://localhost/info/get-user-info?room_id={$room_id}&checkin={$checkin}&checkout={$checkout}";
        $loginUrl    = $helper->getLoginUrl($callback, $permissions);
        return $loginUrl;

    }


}