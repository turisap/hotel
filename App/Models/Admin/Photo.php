<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 20-May-17
 * Time: 1:06 PM
 */

namespace App\Models\Admin;


class Photo extends \Core\Model {


    // supplied with $_FILES and input name of multiply input file
    public function __construct($picture) {

        foreach($picture as $key => $value){
            $this->$key = $value;
            //echo $key;
        }

    }

    // creates a easy-readable array of properties and their values out of multiply-files array from form
    // must be supplied with argument in format $_FILES['input_name']
    public static function reArrayFiles($file_post) {

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