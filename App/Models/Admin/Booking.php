<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 27-May-17
 * Time: 6:06 PM
 */

namespace App\Models\Admin;


use PDO;


class Booking extends \Core\Model {

    protected static $db_table = 'bookings';  // corresponding table in the database
    public $errors = array();                 // array for errors validation


    // constructor to create object's properties and assign values to them out of POST array
    public function __construct($booking)
    {
        // here we creates properties and their values out of keys and values
        foreach($booking as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }


    // this function insert record about a new booking into the database
    public function newBooking(){

        // first validate inputs
        $this->validateFormData();

        // if there is no errors, proceed with inserting data into the database
        if(empty($this->errors)){

            $sql = 'INSERT INTO ' . static::$db_table . ' (room_id, user_id, title, first_name, last_name, num_guests, breakfast,
        smoking, pets, quite_room, bike_rent, checkin, checkout, num_children, arrival_time, wishes) VALUES 
        (:room_id, :user_id, :title, :first_name, :last_name, :num_guests, :breakfast, :smoking, :pets, :quite_room, 
        :bike_rent, :checkin, :checkout, :num_children, :arrival_time, :wishes)';

            $db = static::getDB();

            $stm = $db->prepare($sql);

            // make some vars for values from checkboxes
            $breakfast  = isset($this->breakfast) ?? 0;
            $smoking    = isset($this->smoking) ?? 0;
            $pets       = isset($this->pets) ?? 0;
            $quite_room = isset($this->quite_room) ?? 0;
            $bike_rent  = isset($this->bike_rent)  ?? 0;

            $stm->bindValue(':room_id', $this->room_id, PDO::PARAM_INT);
            $stm->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
            $stm->bindValue(':title', $this->title, PDO::PARAM_STR);
            $stm->bindValue(':first_name', $this->first_name, PDO::PARAM_STR);
            $stm->bindValue(':last_name', $this->last_name, PDO::PARAM_STR);
            $stm->bindValue(':num_guests', $this->num_guests, PDO::PARAM_INT);
            $stm->bindValue(':breakfast', $breakfast, PDO::PARAM_INT);
            $stm->bindValue(':smoking', $smoking, PDO::PARAM_INT);
            $stm->bindValue(':pets', $pets, PDO::PARAM_INT);
            $stm->bindValue(':quite_room', $quite_room, PDO::PARAM_INT);
            $stm->bindValue(':bike_rent', $bike_rent, PDO::PARAM_INT);
            $stm->bindValue(':checkin', $this->checkin, PDO::PARAM_STR);
            $stm->bindValue(':checkout', $this->checkout, PDO::PARAM_STR);
            $stm->bindValue(':num_children', $this->num_children, PDO::PARAM_STR);
            $stm->bindValue(':arrival_time', $this->arrival_time, PDO::PARAM_STR);
            $stm->bindValue(':wishes', $this->wishes, PDO::PARAM_STR);

            return $stm->execute();

        }

        return false;
    }

    // validation of form inputs
    protected function validateFormData(){

        // validation of first and last names
        if(strlen($this->first_name) < 2){
            $this->errors[] = 'First name should be at least 2 characters long';
        }

        if(empty($this->first_name)){
            $this->errors[] = 'First name cannot be empty. Please enter your name';
        }

        if(strlen($this->last_name) < 2){
            $this->errors[] = 'Last name should be at least 2 characters long';
        }

        if(empty($this->last_name)){
            $this->errors[] = 'Last name cannot be empty. Please enter your name';
        }

        // validation of bookings overlapping



    }


    // this method check whether a particular room is booked on a particular date
    public function dateBooked($begin, $finish){

        // first find all bookings to a particular room
        $bookings = Booking::;
    }


    // this method finds all bookings to a particular room
    protected function findAllBookingsToONeRoom(){

        $sql = 'SELECT checkin, checkout FROM ' . static::$db_table . ' WHERE room_id = :room_id';

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->bindValue(':room_id', $this->room_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, static::class);

        $stm->execute();

        return $stm->fetchAll();
    }



}