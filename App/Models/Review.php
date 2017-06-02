<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 02-Jun-17
 * Time: 4:42 PM
 */

namespace App\Models;


use PDO;


class Review extends \Core\Model {

    protected static $db_table = 'Reviews';


    // create a review object using form data
    public function __construct($post=[]) {
        // here we make object's properties and their values out of POST array
        foreach($post as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }


    // this method gets average review by each aspect for a room
    public static function getAverageRatingsForARoom($room_id){


        $sql = 'SELECT cleanliness, comfort, service, food, location, overall FROM ' . static::$db_table . ' WHERE room_id = :room_id';

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, static::class);

        $stm->execute();

        $records = $stm->fetchAll();


        $cleanliness = $comfort = $service = $food = $location = $overall = array(); // array for keeping Reviews values

        // get array for keeping results
        $sets = array(
            'cleanliness'    => $cleanliness,
            'comfort'  => $comfort,
            'service'  => $service,
            'food'     => $food,
            'location' => $location,
            'overall'  => $overall
        );


        // get all records and push them into the sets array
        foreach($records as $record){

            $sets ['cleanliness'][] = $record->cleanliness;
            $sets ['comfort'][] =$record->comfort;
            $sets ['service'][] = $record->service;
            $sets ['food'][] = $record->food;
            $sets ['location'][] = $record->location;
            $sets ['overall'][] = $record->overall;

        }

        // an array for keeping averages
        $averages = array();



        // count averages for each category accordingly and push them into the averages array
        if(count($sets['food']) > 0){

            foreach ($sets as $key => $value){

                $averages[$key] = array_sum($value)/count($value);

            }

        }

        return $averages;

    }


    // finds all revies to a particular room
    public static function findAllReviewsToOneRoom($room_id){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE room_id = :room_id';

        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetchAll();

    }




























}