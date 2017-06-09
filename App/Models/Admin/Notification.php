<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 07-Jun-17
 * Time: 8:02 PM
 */

namespace App\Models\Admin;

use PDO;
use DateTime;
use DateTimeZone;


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
    public static function getAllNotifications($count=false, $unread=false, $limit=false){

        $sql = 'SELECT ' . ($count ? 'COUNT(notification_id)' :  '*') . ' FROM '  . static::$db_table;

        // get all notifications, read only or unread only (0 for all (by default), 1 for unread and 0 for read only)
        $sql .= $unread ? ($unread == 1 ? ' WHERE read_status = 1' : ' WHERE read_status = 0') : '';

        $sql .= $limit ? ' LIMIT ' . $limit : '' ;

        $sql .= $count ? '' : ' ORDER BY timestamp ASC';

        //return $sql;
        $db  = static::getDB();

        $stm = $db->prepare($sql);

        // fetch a class or just a column in the case if we need only the number of notifications
        $count ?  : $stm->setFetchMode(PDO::FETCH_ASSOC);

        $stm->execute();

        $results = $count ? $stm->fetch(PDO::FETCH_COLUMN) : $stm->fetchAll() ;


        if( ! $count){ // only if we didn't set the method just to count notifications

            // array for keeping notificatons with info
            $full_results = array();

            // find and append information about notifications from respective tables
            foreach ($results as $result){

                $action = $result['action'] ?? false;
                $id     = ($result['action'] > 2) ? $result['user_id'] : $result['booking_id'];

                // get notification info
                $info = self::getNotificationsInfo($action, $id);

                // turn timestamp to a message
                $result['timestamp'] = self::getDaysAndHoursAgo($result['timestamp']);

                // merge results into a single array
                $full_results[] = array_merge($result, $info[0]);

            }

            return $full_results;
        }

        return $results;
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


    // this method turns timestamp into "days and hours ago format)
    public static function getDaysAndHoursAgo($timestamp){

        date_default_timezone_set("Asia/Bangkok");

        $today = new DateTime();
        $date = new DateTime($timestamp);

        $diff = $date->diff($today);

        $message =  (($diff->d) > 0) ? $diff->d . ' day(s) and ' . $diff->h . ' ago' : '' ;
        $message .= ($diff->d == 0) ? ($diff->h . ' hours ' .  $diff->i . ' minutes ago') : '';

        return $message;

    }


    // this method assemblies a package for Twig global notifications (to show in navbar)
    public static function getGlobalPackage(){

        return [
            'count' => self::getAllNotifications(true, true, false),
           // 'notifications'           => self::getAllNotifications(false, true, 3)
        ];

    }




}