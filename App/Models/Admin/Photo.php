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
    protected static $upload_derictory = 'public/uploads/pictures/rooms'; // path to uploaded pictures
                        //protected static $path = 'Pictures';
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
    public function save($room_id){

        // first validate uploaded file
        $this->validatePhoto();

        if(empty($this->errors_on_upload)){ // insert data only if array with errors is empty

            $sql = 'INSERT INTO ' . static::$db_table . ' (room_id, name, type, size) VALUES (:room_id, :name, :type, :size)';
            $db  = static::getDB();

            $stm = $db->prepare($sql);

            $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
            $stm->bindValue(':name', time() . $this->name, PDO::PARAM_STR);
            $stm->bindValue(':type', $this->type, PDO::PARAM_STR);
            $stm->bindValue(':size', $this->size, PDO::PARAM_INT);

            return $stm->execute();

        }
        return false; // on failure
    }

    // this method validates pictures on upload
    protected function validatePhoto(){

        $extension = $this->type;

        if($extension != 'image/jpeg' && $extension != 'image/png' && $extension != 'image/jpg'){
            $this->errors_on_upload[] = "Your file should be .jpeg, .jpg or .png";
        }
        if(empty($this->name)){
            $this->errors_on_upload[] = "There is no file to download";
        }
        if($this->size > Config::MAX_FILE_SIZE){
            $this->errors_on_upload[] = "Your picture shouldn't be more than 10 Mb";
        }

        if($this->error != 0) { //0 means no error, so if otherwise, display a respective message
            $this->errors_on_upload[] = $this->upload_errors_array[$this->error];
        }

    }




}