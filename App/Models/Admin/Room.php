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
    public static $column = 'id';


    // create a room object using form data
    public function __construct($post=[], $files=[]) {
        // here we make object's properties and their values out of POST array
        foreach($post as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }

    // saves room in the database
    public function save($update = false, $room_id = null){

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
            $tv = isset($this->tv) ? $this->tv : 0;

            if( ! $update){
                $sql = 'INSERT INTO ' . static::$db_table . ' (room_number, local_name, num_beds, num_rooms, num_guests, view,
                description, area, class, cancel_days, children, pets, aircon, smoking, tv) VALUES (:room_number, :local_name,
                :num_beds, :num_rooms, :num_guests, :view, :description, :area, :class, :cancel_days, :children, :pets,
                :aircon, :smoking, :tv)';

            } else {

                $sql = 'UPDATE ' . static::$db_table . 'SET (room_number = :room_number, local_name = :local_name, num_beds = :num_beds, num_rooms = :num_rooms,
                num_guests = :num_guests, view = :view, description = :description, area = :area, class = :class, cancel_days = :cancel_days,
                children = :children, pets = :pets, aircon = :aircon, smoking = :smoking, tv = :tv) WHERE id = :id';

            }

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
            $stm->bindValue(':tv', $tv, PDO::PARAM_INT);
            if($update){$stm->bindValue(':id', $room_id, PDO::PARAM_INT);}

            if( ! $update) {
                return $stm->execute() ? $db->lastInsertId() : false;    // return the last inserted id on success and false on failure
            } else {
                return $stm->execute();
            }


        }

        return false; // on failure
    }


    // this method validates data from inputs (not files)
    public function validateData(){

        if($this->room_number == ''){
            $this->errors[] = 'Please enter a room number';
        }

        if(static::numberExists($this->room_number)){
            $this->errors[] = 'This room number is already taken';
        }

        if($this->local_name == ''){
            $this->errors[] = 'Please enter a local name for this room';
        }

        if(strlen($this->local_name) < 5){
            $this->errors[] = 'Local name should be at least 5 characters long';
        }

        if( ! preg_match('/\d+/', $this->area)){
            $this->errors[] = 'Area should be a number';
        }

    }

    // checks whether there is a room with such a number in the database through ajax from create room form
    public static function numberExists($number, $id_ignore = null){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE room_number = :room_number';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':room_number', $number, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        $room = $stm->fetch();


        if($room && $room->id != $id_ignore){
            return true;
        }

        return false;

    }


    // returns array with all pictures and rooms objects
    public static function findAllRoomsWithPhotos(){

        // get all rooms
        $rooms = static::findAll();



        $rooms_with_photos = array(); // array for keeping pictures and photos objects together

        foreach ($rooms as $room){

            $set = array(); // array for each set of a room and respective pictures

              $pictures = Photo::findAllPhotosToONeRoom($room->id,  true ); // find all pictures to a particular room

            if(count($pictures) < 4){

                $set = [
                    'room'     => $room,
                    'pictures' => Array ( 'id' => 0, 'room_id' => 0, 'main' => 1, 'name' => '149534948934699692.jpg', 'type' => 'image/jpeg', 'size' => 85505, 'path' => '/uploads/pictures/rooms/room_placeholder.jpg')
                ];

            } else {

                $set = [
                    'room'     => $room,
                    'pictures' => $pictures
                ];

            }


            $rooms_with_photos[] = $set;

        }

        return $rooms_with_photos;
        //return (empty($rooms_with_photos[0])) ? false : $rooms_with_photos;



    }


    /*public function updateRoom($data, $room_id){

        // first assign data from POST to the object's properties
        $this->local_name  = $data['local_name'];
        $this->room_number = $data['room_number'];
        $this->area        = $data['area'];
        $this->description = $data['description'];
        $this->beds        = $data['beds'];
        $this->rooms       = $data['rooms'];
        $this->guests      = $data['guests'];
        $data['class'] == 0 ? $this->class = 'No specified' : $this->class = $data['class'];
        $data['cancel_days'] == 0 ? $this->cancel_days = 'No specified' : $this->cancel_days = $data['cancel_days'];
        $data['view'] == 0 ? $this->view = 'No specified' : $this->view = $data['view'];
        isset($data['pets']) ? $this->pets = 1 : $this->pets = 0;
        isset($data['aircon']) ? $this->aircon = 1 : $this->aircon = 0;
        isset($data['smoking']) ? $this->children = 1 : $this->children = 0;
        isset($data['tv']) ? $this->tv = 1 : $this->tv = 0;

        $sql = 'UPDATE INTO ' . static::$db_table . ' (room_number, local_name, num_beds, num_rooms, num_guests, view,
             description, area, class, cancel_days, children, pets, aircon, smoking, tv) VALUES (:room_number, :local_name,
              :num_beds, :num_rooms, :num_guests, :view, :description, :area, :class, :cancel_days, :children, :pets,
              :aircon, :smoking, :tv) WHERE id = :id';

        $db  = static::getDB();
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
        $stm->bindValue(':tv', $tv, PDO::PARAM_INT);

    }*/






}