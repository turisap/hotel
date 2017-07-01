<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-Jul-17
 * Time: 10:13 AM
 */

namespace App\Controllers;



class Info
{

    public static function getUserInfo() {

        //print_r(dirname(__DIR__, 2) . '/Facebook/src/autoload.php');
        //require_once(dirname(__DIR__, 2) . '/Facebook/src/Facebook/autoload.php');

        $fb = new Facebook\Facebook([
            'app_id' => '321611971611782',
            'app_secret' => '7151d1bf1477b4c8e7cd09540d0e74ce',
            'default_graph_version' => 'v5.5'
        ]);

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', '{access-token}');
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphUser();

        echo 'Name: ' . $user['name'];

    }
}