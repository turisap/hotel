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
    public static $column = 'id';
    protected static $upload_directories = ['public\uploads\pictures\rooms', 'public\uploads\pictures\courses']; // path to uploaded pictures
    protected static $directory_pathes = ['/uploads/pictures/rooms', '/uploads/pictures/courses'];
    protected static $path_to_unlink = 'uploads/pictures/rooms/';
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
    public function __construct($picture=[]) {

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
    public function save($room_id=[], $i=[], $course_id =[]){

        // first validate uploaded file
        $this->validatePhoto();

        if(empty($this->errors_on_upload)){ // insert data only if array with errors is empty

            $this->filename = time() . $this->name;

            $sql = 'INSERT INTO ' . static::$db_table;

            $sql .= $room_id ? ' (room_id, main, name, type, size, path) VALUES ' : ''; // these values only for rooms' photos

            $sql .= $course_id ? ' (course_id, name, type, size, path) VALUES ' : '';  // these values only for courses' photos

            $sql .= $room_id ? '(:room_id, :main,' : ''; // these values only for rooms' photos

            $sql .= $course_id ? ' (:course_id, ' : '' ; // this value only for courses' photos

            $sql .= ':name, :type, :size, :path)';

            $db  = static::getDB();

            $stm = $db->prepare($sql);


            $room_id ? $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT) : ''; // bind this value only for room photos for room saving (not for photos for restaurant's courses)
            $i ? $stm->bindValue(':main', (($i === 1) ? true : false), PDO::PARAM_INT) : ''; //counter for the main photo, bind this value only for room photos, not for photos in restaurant courses
            $course_id ? $stm->bindValue(':course_id', $course_id, PDO::PARAM_INT) : ''; // bind this value only those photos which ara being saved from restaurant's courses
            $stm->bindValue(':name', $this->filename, PDO::PARAM_STR);
            $stm->bindValue(':type', $this->type, PDO::PARAM_STR);
            $stm->bindValue(':size', $this->size, PDO::PARAM_INT);
            $stm->bindValue(':path', ($room_id ? $this->pathToPicture(0) : $this->pathToPicture(1)), PDO::PARAM_STR);

            //return $sql;

            $stm->execute();

            $target_path = dirname(__DIR__, 3) . Config::DS . ($room_id ? static::$upload_directories[0] : static::$upload_directories[1]) . Config::DS . $this->filename;
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



    protected function pathToPicture($derictory){
        return static::$directory_pathes[$derictory] . Config::DS . $this->filename;
    }




    public static function findAllPhotosToONeRoom($room_id, $main_photo_only){

        $sql  = 'SELECT * FROM photos WHERE room_id = :id'; // we need to pass only the main picture to all_rooms template
        $sql .= $main_photo_only ? ' AND main = 1' : '';    // get only main photo or all

        $db  = static::getDB();

        $stm = $db->prepare($sql);

        $stm->bindValue(':id', $room_id, PDO::PARAM_INT);



        if(($stm->execute())){


            if($main_photo_only){        // fetch all or only one row (for all_rooms page or for a particular room page)

                $picture = $stm->fetch();

                if($picture){

                    return $picture;


                } else { //return placeholder
                     return Array ('name' => '149534948934699692.jpg', 'type' => 'image/jpeg', 'size' => 85505, 'path' => '/uploads/pictures/rooms/room_placeholder.jpg');
                }



            } else {
                $pictures = $stm->fetchAll();

                return $pictures;
            }



        } else {
            // return a sample array with placeholder on failure instead of false to avoid further use 'false' as an array
            return false;
        }
        

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


    // this method deletes images from upload folder
    public static function unlinkImages($filename){
        return unlink(static::$path_to_unlink . $filename);
    }


    // finds a photo by a course id
    public static function findPhotoByCourseId($course_id){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE course_id = :id';

        $db  = static::getDB();
        $stm = $db->prepare($sql);
        $stm->bindValue(':id', $course_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, self::class);

        $stm->execute();

        return $stm->fetch();

    }



}