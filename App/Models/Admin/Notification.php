<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 07-Jun-17
 * Time: 8:02 PM
 */

namespace App\Models\Admin;

use App\Config;
use PDO;
use DateTime;


class Notification extends \Core\Model {

    public static $db_table = 'notifications';
    public static $column = 'notification_id';



    public function __construct($item=[]) {

        // here we creates properties and their values out of keys and values
        foreach($item as $key => $value){
            $this->$key = $value;
            //echo $key;
        }

    }


    // this method gets all unread notifications
    public static function getAllNotifications($count=false, $unread=false, $limit=false, $offset=false){

        $sql = 'SELECT ' . ($count ? 'COUNT(notification_id)' :  '*') . ' FROM '  . static::$db_table;

        // get all notifications, read only or unread only (0 for all (by default), 1 for unread and 0 for read only)
        $sql .= $unread ? ($unread == 1 ? ' WHERE read_status = 1' : ' WHERE read_status = 0') : '';

        $sql .= $count ? '' : ' ORDER BY timestamp ASC';

        $sql .= $limit ? ' LIMIT ' . $limit : '' ;

        // offset for pagination (LIMIT was presetn before)
        $sql .= ($offset && $offset > 0) ? ' OFFSET ' . $offset : '';

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

                //return array($action, $id);

                // get notification info
                $info = self::getNotificationsInfo($action, $id);

                // turn timestamp to a message
                $result['timestamp'] = self::getDaysAndHoursAgo($result['timestamp']);

                //return $info;

                // merge results into a single array (only if info found, it isn't in the case of manual deletion of booking or a user from the database)
                if(!empty($info)){
                    $full_results[] = array_merge($result, $info[0]);
                }

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

        //return $sql;

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

        $message =  (($diff->d) > 1) ? $diff->d . ' days and ' . $diff->h . ' hours ago' : (($diff->d == 1) ? $diff->d . ' day and ' . $diff->h . ' hours ago' : '');
        $message .= ($diff->d == 0 && $diff->h != 0) ? ($diff->h . ' hours ' .  $diff->i . ' minutes ago') : ( ($diff->d == 0 && $diff->h == 0) ? ($diff->i . ' minutes ago') : '');

        return $message;

    }


    // this method assemblies a package for Twig global notifications (to show in navbar)
    public static function getGlobalPackage(){

        return [
            'count' => self::getAllNotifications(true, true, false),
            'notifications'           => self::getAllNotifications(false, 1, 4)
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

            $sql = 'SELECT notification_id, booking_id, user_id, action FROM notifications WHERE notification_id = ?';
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


    // this method counts how many bookings and how many bookings, users and so on in notifications array based on previous method
    public static function getTypesCount($notifications){

        // initialize array for keeping counts
        $events = [
            'cancellations'   => 0,
            'new_bookings'    => 0,
            'new_users'       => 0,
            'activated_users' => 0,
            'booking_ids'      => []
        ];

        foreach ($notifications as $notification){

            if(isset($notification->action)){ // there are maybe deleted notifications while their ids are still in the SESSION

                switch ($notification->action) {

                    case "1":
                        $events['new_bookings']++;
                        $events['booking_ids'][] = $notification->booking_id; // add IDs of new bookings
                        break;
                    case "2":
                        $events['cancellations']++;
                        $events['booking_ids'][] = $notification->booking_id; // add cancelled booking IDs
                        break;
                    case "3":
                        $events['activated_users']++;
                        break;
                    case "4":
                        $events['new_users']++;
                        break;
                    default:
                        return false;
                }

            }



        }

        return $events;

    }


    // this method deletes old notifications from the database
    public static function deleteOldNotifications(){

        $today = new DateTime();
        $today->modify(Config::DAYS_KEEP_NOTIFICATIONS);
        $timestamp = $today->format('Y-m-d H:i:s');

        $sql = "DELETE FROM " . static::$db_table . " WHERE timestamp < '" . $timestamp . "'";

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->execute();

    }




}