<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 27-May-17
 * Time: 6:06 PM
 */

namespace App\Models\Admin;


use App\Config;
use PDO;
use DateTime;


class Booking extends \Core\Model {

    protected static $db_table = 'bookings';  // corresponding table in the database
    public $errors = array();                 // array for errors validation
    public static $column = 'booking_id';


    // constructor to create object's properties and assign values to them out of POST array
    public function __construct($booking=[])
    {
        // here we creates properties and their values out of keys and values
        foreach($booking as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }


    // this function insert record about a new booking into the database
    public function newBooking($room_name){

        // first validate inputs
        $this->validateFormData();

        // if there is no errors, proceed with inserting data into the database
        if(empty($this->errors)){

            $sql = 'INSERT INTO ' . static::$db_table . ' (room_id, user_id, room_name, title, first_name, last_name, num_guests, breakfast,
        smoking, pets, quite_room, bike_rent, checkin, checkout, num_children, arrival_time, wishes) VALUES 
        (:room_id, :user_id, :room_name, :title, :first_name, :last_name, :num_guests, :breakfast, :smoking, :pets, :quite_room, 
        :bike_rent, :checkin, :checkout, :num_children, :arrival_time, :wishes)';

            $db = static::getDB();

            $stm = $db->prepare($sql);

            // make some vars for values from checkboxes
            $breakfast  = isset($this->breakfast) ?? 0;
            $smoking    = isset($this->smoking) ?? 0;
            $pets       = isset($this->pets) ?? 0;
            $quite_room = isset($this->quite_room) ?? 0;
            $bike_rent  = isset($this->bike_rent)  ?? 0;
            $num_children = isset($this->num_children) ? $this->num_children : null;

            $stm->bindValue(':room_id', $this->room_id, PDO::PARAM_INT);
            $stm->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
            $stm->bindValue(':room_name', $room_name, PDO::PARAM_STR);
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
            $stm->bindValue(':num_children', $num_children, PDO::PARAM_STR);
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
        if(($this->datesBooked($this->checkin, $this->checkout))){
            $this->errors[] = 'Sorry but these dates are already booked';
        }

        // validation of empty datepickers
        if(empty($this->checkin)){
            $this->errors[] = 'Please enter the check in date';
        }

        if(empty($this->checkout)){
            $this->errors[] = 'Please enter the check out date';
        }


    }


    // this method check whether a particular room is booked on a particular date
    public function datesBooked($begin, $finish){


        // there are no overlaps if both checkCheckInDate() and checkCheckOutDate() returns true
        if($this->isBookedCheckinDate($begin, $this->room_id) || $this->isBookedCheckOutDate($finish, $this->room_id)){
            return true;
        }

        return false; // on failure

    }



    // check only checkin date in order not to proceed with checkout date if its in the range of an existing booking
    public static function isBookedCheckinDate($checkin, $room_id){

        // get time values out of strings from datepickers
        $checkin  = strtotime($checkin);

        // first find all bookings to a particular room
        $bookings = self::findAllBookingsToONeRoom($room_id);

        // if there are some bookings over here
        if($bookings){

            // loop through bookings to check if there are overlaps
            foreach($bookings as $booking){

                if($booking->status != 2 && $booking->status !=0){     // check whether a booking was cancelled or already in the past

                    // if checkin date in the range of an existing booking (only for upcoming and not cancelled)
                    if ($checkin >= strtotime($booking->checkin) && $checkin < strtotime($booking->checkout)){
                        return true;
                    }

                }


            }

            return false; // return true if there are no overlaps
        }
        return false; // return true if there are no bookings

    }


    // this method checks whether check out date hits other bookings ranges
    public static function isBookedCheckOutDate($checkout, $room_id){

        // get time values out of strings from datepickers
        $checkout = strtotime($checkout);

        // first find all bookings to a particular room
        $bookings = self::findAllBookingsToONeRoom($room_id);

        // if there are some bookings over here
        if($bookings){

            // loop through bookings to check if there are overlaps
            foreach($bookings as $booking){

                if($booking->status != 2 && $booking->status !=0){     // check whether a booking was cancelled or already in the past

                    // if checkout date later than a booking's checkin and earlier than it's checkout(only for upcoming and not cancelled)
                    if ($checkout > strtotime($booking->checkin) && $checkout <= strtotime($booking->checkout) ){
                        return true;
                    }

                }


            }

            return false; // return true if there are no overlaps
        }
        return false; // return true if there are no bookings

    }


    // this method finds all bookings to a particular room
    public static function findAllBookingsToONeRoom($room_id, $limit = false, $only_future = false){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE room_id = :room_id';

        $sql .= $only_future ? ' AND status = 1 ' : ''; // append only upcoming if it was specified

        $sql .= $limit ? ' LIMIT ' . $limit : ''; // append limit statement if it was supplied

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetchAll();
    }


    // checks status of all bookings and sets it to 0 (past) if it check in date in the past
    public static function checkAndChangeAllBookingsStatus(){

        // find all booking objects first
        $bookings = self::findAll();

        if($bookings){

            // check statuses for all bookings
            foreach ($bookings as $booking) {

                // compare checkin date with the current time and check status whether it was canceled
                if (strtotime($booking->checkin) < time() && $booking->status != 2){

                    self::cancelBooking($booking->booking_id, false);

                }

            }

        }
    }


    // this method cancel bookings (sets status to 2 but doesn't delete it)
    public static function cancelBooking($booking_id, $cancel = true){

        // check first is cancelation is possible
        if(self::isCancellationPossible($booking_id)){

                // first set condition for cancelation or setting booking as past
                $cancel_or_past = $cancel ? 2 : 0;

                $sql = 'UPDATE ' . static::$db_table . ' SET status = ' . $cancel_or_past . ' WHERE booking_id = :id';

                $db  = static::getDB();
                $stm = $db->prepare($sql);
                $stm->bindValue(':id', $booking_id, PDO::PARAM_INT);

                return $stm->execute();

        }

        return false;
    }


    // this method checks if cancellation is possible ( more days before cancelation in comparison what was specified)
    public static function isCancellationPossible($booking_id){

        // first get that booking
        $booking = self::findById($booking_id);

        if($booking){

            // check the number of cancelation days was specified
            $room = Room::findById($booking->room_id);
            $cancel_days = ($room->cancel_days == 0) ? 1 : $room->cancel_days;

            if((strtotime($booking->checkin)) > (time()+ ($cancel_days * 24 * 60 * 60))){
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }

    }


    // deletes all bookings which is older than the specified data
    public static function automaticBookingsDeletion(){


        $today = new DateTime();
        $limit_stamp = $today->modify(Config::DAYS_BOOKING_KEEPING);
        $limit_date = $limit_stamp->format('Y-m-d');


        $sql = 'DELETE FROM ' . static::$db_table . ' WHERE checkin < \'' . $limit_date. '\'';

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->execute();

    }


    // this method check whether a room has an upcoming booking in order to create notification
    public static function upcomingBooking($room_id){

        // set up the date when a booking becomes upcoming
        $today = new DateTime();
        $limit_stamp = $today->modify(Config::UPCOMING_LIMIT);
        $limit_date = $limit_stamp->format('Y-m-d');

        $sql = 'SELECT COUNT(booking_id) FROM ' . static::$db_table . ' WHERE room_id = ' . $room_id . ' AND status = 1 AND checkin <= ' . '\'' . $limit_date . '\'';

        $db  = static::getDB();

        $stm = $db->prepare($sql);

        $stm->execute();

        return $stm->fetchColumn();

    }


    // this method checks if there are upcoming bookings which became ongoing
    public static function isThereOngoingBookings(){

        // first to set this function to run only if $_SESSION with checking isn't set (to check this ongoing only once in a session)
        //if(!isset($_SESSION['ongoing'])){

            // set session to true in order to avoid checking during a session
            $_SESSION['ongoing'] = true;

            $today = new DateTime();
            $today = $today->format('Y-m-d H:i:s');

            $sql = "UPDATE " . static::$db_table . " SET status = 3 WHERE checkin <= :today AND checkout > :today";
            $db  = static::getDB();

            $stm = $db->prepare($sql);
            $stm->bindValue(':today', $today, PDO::PARAM_STR);
            $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            $stm->execute();

        //}
        return false;
    }





}