<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 4:29 PM
 */

namespace App\Controllers;

use \App\Authentifiacation;
use App\Mail;
use \App\Token;
use App\Models\RememberedLogin;


class Test
{


    public static function test(){

        print_r(self::reArrayFiles($_FILES['photos']));

        //print_r($_FILES);


        /*$file_count = count($_FILES['photos']);
        $file_keys = array_keys($_FILES['photos']);
        print_r($file_count);
        echo '<br>';
        print_r($file_keys);*/

    }




    protected static function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i < $file_count; $i++){
            foreach ($file_keys as $key){
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }


        return $file_ary;
    }


}