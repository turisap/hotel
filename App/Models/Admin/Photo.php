<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 20-May-17
 * Time: 1:06 PM
 */

namespace App\Models\Admin;

use PDO;


class Photo extends \Core\Model {

    public static $db_table = 'photos';


    // supplied with $_FILES and input name of multiply input file
    public function __construct($picture) {

            // here we creates properties and their values out of keys and values
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

    // this function saves photos to the database
    public function save($room_id){

        $sql = 'INSERT INTO ' . static::$db_table . ' (room_id, name, type, size) VALUES (:room_id, :name, :type, :size)';
        $db  = static::getDB();

        $stm = $db->prepare($sql);

        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stm->bindValue(':name', time() . $this->name, PDO::PARAM_STR);
        $stm->bindValue(':type', $this->type, PDO::PARAM_STR);
        $stm->bindValue(':size', $this->size, PDO::PARAM_INT);

       return $stm->execute();

    }




}