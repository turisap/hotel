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
use App\Models\Admin\Photo;
use App\Models\Admin\Search;
use \App\Token;
use App\Models\RememberedLogin;
use App\Models\Admin\Room;


class Test
{


    /*public static function test(){

        print_r(Photo::test());

        //print_r($_FILES);


        /*$file_count = count($_FILES['photos']);
        $file_keys = array_keys($_FILES['photos']);
        print_r($file_count);
        echo '<br>';
        print_r($file_keys);

    }*/

    public function test(){
        print_r(Search::findSearchSubcategories('all'));
    }





    // creates a easy-readable array of properties and their values out of multiply-files array from form
    protected static function reArrayFiles($file_post) {

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