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

        $sql .= $count ? '' : ' ORDER BY timestamp ASC';

        $sql .= $limit ? ' LIMIT ' . $limit : '' ;

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

        $message =  (($diff->d) > 0) ? $diff->d . ' day(s) and ' . $diff->h . ' hours ago' : '' ;
        $message .= ($diff->d == 0 && $diff->h != 0) ? ($diff->h . ' hours ' .  $diff->i . ' minutes ago') : ( ($diff->d == 0 && $diff->h != 0) ? ($diff->i . ' minutes ago') : '');

        return $message;

    }


    // this method assemblies a package for Twig global notifications (to show in navbar)
    public static function getGlobalPackage(){

        return [
            'count' => self::getAllNotifications(true, true, false),
            'notifications'           => self::getAllNotifications(false, true, 4)
        ];

    }


    // this method sets a notification as viewed
    public static function setAsRead($notification_id){

        $sql = 'UPDATE ' . static::$db_table . ' SET read_status = 0 WHERE notification_id = ' . $notification_id;

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        return $stm->execute();

    }



    // this method assemblies a message for /admin/home/index about what happened since last visit
    public static function sinceLastVisit(){

        // we assume this is th beginning of a new visit only if $_SESSION['new_notifications'] isn't set
        $notification_ids = $_SESSION['new_notifications'] ?? false;

        if( ! $notification_ids){

            // first get all notifications with view_status equal to one
            $sql = 'SELECT notification_id FROM notifications WHERE view_status = 1';

            $db  = static::getDB();
            $stm = $db->prepare($sql);

            $stm->execute();

            $notifications = $stm->fetchAll(PDO::FETCH_COLUMN);

            if($notifications){

                // if there are results, set them as viewed in the database
                self::setAllAsViewed();

                // set session to show these notifications during session
                $_SESSION['new_notifications'] = $notifications;

                return true;

            }


        }


        return false;

    }



    // this method sets all notifications as viewed
    protected static function setAllAsViewed(){

        $sql = 'UPDATE notifications SET view_status = 0';
        $db  = static::getDB();

        $stm = $db->prepare($sql);

        return $stm->execute();

    }


    // this method gets all notifications based on ids in session from previous method
    public static function showUnviewedNotifications(){


        $notification_ids = $_SESSION['new_notifications'] ?? false;

        if($notification_ids) {

            $sql = 'SELECT * FROM notifications WHERE notification_id = ?';
            $db  =  static::getDB();

            $stm = $db->prepare($sql);
            $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            $notifications = array();

            foreach ($notification_ids as $notification_id){

                $stm->execute([$notification_id]);
                $notifications[] = $stm->fetch();

            }

            return $notifications;

        }
        return false;
    }


}