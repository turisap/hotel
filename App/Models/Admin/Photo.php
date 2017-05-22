<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 20-May-17
 * Time: 1:06 PM
 */

namespace App\Models\Admin;

use PDO;
use \App\Config;


class Photo extends \Core\Model {

    public $errors_on_upload = [];                          // array for saving error messages
    public static $db_table = 'photos';                     // database table
    protected static $upload_derictory = 'public\uploads\pictures\rooms'; // path to uploaded pictures
    protected static $path = '/uploads/pictures/rooms';
    public $upload_errors_array = array(


        UPLOAD_ERR_OK            => "There is no errors",
        UPLOAD_ERR_INI_SIZE      => "The uploaded file exceeds max size",
        UPLOAD_ERR_FORM_SIZE     => "The uploaded file exceeds max size of form post request",
        UPLOAD_ERR_PARTIAL       => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_FILE       => "No file was uploaed",
        UPLOAD_ERR_NO_TMP_DIR    => "Missing the temporary folder",
        UPLOAD_ERR_CANT_WRITE    => "Failed to write to disk",
        UPLOAD_ERR_EXTENSION     => "A php extension stopped the file upload",

    );


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
    public function save($room_id, $i){

        // first validate uploaded file
        $this->validatePhoto();

        if(empty($this->errors_on_upload)){ // insert data only if array with errors is empty

            $this->filename = time() . $this->name;

            $sql = 'INSERT INTO ' . static::$db_table . ' (room_id, main, name, type, size, path) VALUES (:room_id, :main, :name, :type, :size, :path)';
            $db  = static::getDB();

            $stm = $db->prepare($sql);

            $i = ($i === 0) ? true : false;

            $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
            $stm->bindValue(':main', $i, PDO::PARAM_INT);
            $stm->bindValue(':name', $this->filename, PDO::PARAM_STR);
            $stm->bindValue(':type', $this->type, PDO::PARAM_STR);
            $stm->bindValue(':size', $this->size, PDO::PARAM_INT);
            $stm->bindValue(':path', $this->pathToPicture(), PDO::PARAM_STR);

            $stm->execute();

            $target_path = dirname(__DIR__, 3) . Config::DS . static::$upload_derictory . Config::DS . $this->filename;
            if(file_exists($target_path)){
                $this->errors_on_upload[] = 'This file already exists in this directory';
                return false;
            }

            if( !empty($this->tmp_name)){ // if tmp_name is empty, we just don't upload files

                if( ! move_uploaded_file($this->tmp_name, $target_path)){
                    $this->errors_on_upload[] = 'The folder probably doesnt have permissions';
                } else {
                    return true;
                }

            }

        }
        return false; // on failure
    }



    // this method validates pictures on upload
    protected function validatePhoto(){

        $extension = $this->type;

        if( !empty($extension)){
            if($extension != 'image/jpeg' && $extension != 'image/png' && $extension != 'image/jpg'){
                $this->errors_on_upload[] = "Your file should be .jpeg, .jpg or .png";
            }
        }

        if($this->size > Config::MAX_FILE_SIZE){
            $this->errors_on_upload[] = "Your picture shouldn't be more than 10 Mb";
        }

        if($this->error != 0 && $this->error != 4) { //0 means no error, so if otherwise, display a respective message, 4 no files to upload, we allow that
            $this->errors_on_upload[] = $this->upload_errors_array[$this->error];
        }

    }



    protected function pathToPicture(){
        return static::$path . Config::DS . $this->filename;
    }




    public static function findAllPhotosToONeRoom($room_id, $main_photo_only){

        $sql  = 'SELECT * FROM photos WHERE room_id = :id'; // we need to pass only the main picture to all_rooms template
        $sql .= $main_photo_only ? ' AND main = 1' : '';    // get only main photo or all

        $db  = static::getDB();

        $stm = $db->prepare($sql);

        $stm->bindValue(':id', $room_id, PDO::PARAM_INT);

        if($stm->execute()){

            $pictures = $stm->fetchAll();

            return $pictures;

        }

        return false;

    }

    // sets photo as a main by id and unsets prev main one
    public static function setPictureAsMain($picture_id, $room_id){

        // check whether id's were supplied by AJAX request
        if($picture_id !== null && $room_id !== null){

            // first set old main photo main column to 0
            if(static::unsetMainPhoto($room_id)){

                // set chosen photo as main
                $sql = 'UPDATE ' . static::$db_table . ' SET main = 1 WHERE id = :picture_id';
                $db  = static::getDB();

                $stm = $db->prepare($sql);
                $stm->bindValue(':picture_id', $picture_id, PDO::PARAM_INT);

                return $stm->execute();

            }

        }

         return false;

    }

    // unsets main photo of a room
    public static function unsetMainPhoto($room_id){

        $sql = 'UPDATE ' . static::$db_table . ' SET main = 0 WHERE room_id = :room_id AND main = 1';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);

        return $stm->execute();
    }


    // this method adds photos to an existing room
    public static function addPhotos($room_id, $pictures){

        // array for errors
        $errors_on_update = array();

        foreach ($pictures as $picture){

           if($picture->save($room_id, true)){

               $errors_on_update[] = true;

           }

        }

        return in_array(0, $errors_on_update, false) ? false : true;

    }



}