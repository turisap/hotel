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
        $count ? $stm->setFetchMode(PDO::FETCH_COLUMN, 0) : $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetchAll();
    }




}