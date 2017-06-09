<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 07-Jun-17
 * Time: 8:02 PM
 */

namespace App\Models\Admin;

use PDO;


class Notification extends \Core\Model {

    public static $db_table = 'notifications';



    public function __construct($item=[]) {

        // here we creates properties and their values out of keys and values
        foreach($item as $key => $value){
            $this->$key = $value;
            //echo $key;
        }

    }


    // this method gets all unread notifications
    public static function getAllNotifications($count=false, $unread=false){

        $sql = 'SELECT ' . ($count ? 'COUNT(notification_id)' :  '*') . ' FROM '  . static::$db_table;

        // get all notifications, read only or unread only
        $sql .= $unread ? ($unread == 1 ? ' WHERE read_status = 1' : ' WHERE read_status = 0') : '';

        //return $sql;
        $db  = static::getDB();

        $stm = $db->prepare($sql);

        // fetch a class or just a column in the case if we need only the number of notifications
        $count ? $stm->setFetchMode(PDO::FETCH_COLUMN, 0) : $stm->setFetchMode(PDO::FETCH_ASSOC);

        $stm->execute();

        $results = $stm->fetchAll();

        // array for keeping notificatons with info
        $full_results = array();

        // find and append information about notifications from respective tables
        foreach ($results as $result){

            $action = $result['action'] ?? false;
            $id     = ($result['action'] > 2) ? $result['user_id'] : $result['booking_id'];

            // get notification info
            $info = self::getNotificationsInfo($action, $id);

            // merge results into a single array
            $full_results[] = array_merge($result, $info[0]);

        }

        return $full_results;
    }


    // supplies simple info about notification from other tables
    public static function getNotificationsInfo($action, $id){

        // choose a table and columns based on action parameter
        $sql = 'SELECT ' . (($action > 2) ? 'first_name, last_name ' : 'room_name, title, first_name, last_name, checkin, checkout') . ' FROM ' . (($action > 2) ? 'users' : 'bookings') . ' WHERE ' . (($action > 2) ? 'id' : 'booking_id') . ' = :id';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_ASSOC);
        $stm->execute();

        return $stm->fetchAll();

    }




}