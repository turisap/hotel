<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 19-May-17
 * Time: 7:26 PM
 */

namespace App\Models\Admin;


use PDO;

class Room extends \Core\Model {

    public $errors = []; // array for validation errors
    protected static $db_table = 'rooms';


    // create a room object using form data
    public function __construct($post=[], $files=[]) {
        // here we make object's properties and their values out of POST array
        foreach($post as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
        foreach($files as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }

    // saves room in the database
    public function save(){

        // validate data first
        $this->validateData();

        // validation is ok only if errors array is empty
        if(empty($this->errors)){

            // this values from checkboxes and if unchecked, there are no such properties assigned through constructor.
            // so set them to null if they are not set
            $children = isset($this->children) ? $this->children : 0;
            $pets = isset($this->pets) ? $this->pets : 0;
            $aircon = isset($this->aircon) ? $this->aircon : 0;
            $smoking = isset($this->smoking) ? $this->smoking : 0;

            $sql = 'INSERT INTO ' . static::$db_table . ' (room_number, local_name, num_beds, num_rooms, num_guests, view,
             description, area, class, cancel_days, children, pets, aircon, smoking) VALUES (:room_number, :local_name,
              :num_beds, :num_rooms, :num_guests, :view, :description, :area, :class, :cancel_days, :children, :pets,
              :aircon, :smoking)';

            $db = static::getDB();
            $stm = $db->prepare($sql);

            $stm->bindValue(':room_number', $this->room_number, PDO::PARAM_STR);
            $stm->bindValue(':local_name', $this->local_name, PDO::PARAM_STR);
            $stm->bindValue(':num_beds', $this->beds, PDO::PARAM_INT);
            $stm->bindValue(':num_rooms', $this->rooms, PDO::PARAM_INT);
            $stm->bindValue(':num_guests', $this->guests, PDO::PARAM_INT);
            $stm->bindValue(':view', $this->view, PDO::PARAM_STR);
            $stm->bindValue(':description', $this->description, PDO::PARAM_STR);
            $stm->bindValue(':area', $this->area, PDO::PARAM_INT);
            $stm->bindValue(':class', $this->class, PDO::PARAM_STR);
            $stm->bindValue(':cancel_days', $this->cancel_days, PDO::PARAM_INT);
            $stm->bindValue(':children', $children, PDO::PARAM_INT);
            $stm->bindValue(':pets', $pets, PDO::PARAM_INT);
            $stm->bindValue(':aircon', $aircon, PDO::PARAM_INT);
            $stm->bindValue(':smoking', $smoking, PDO::PARAM_INT);

            return $stm->execute();

        }

        return false; // on failure
    }


    // this method validates data from inputs (not files)
    public function validateData(){

        //if($this->);

    }

    // checks whether there is a room with such a number in the database
    public static function numberExists($number){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE room_number = :room_number';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':room_number', $number, PDO::PARAM_STR);

        $stm->execute();

        return $stm->fetch();

    }







}