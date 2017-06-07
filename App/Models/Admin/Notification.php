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
    public static function getAllUnreadNotifications($count=false){

        $sql = 'SELECT';

        // add count statement if it was required
        $sql .= $count ? ' COUNT(notification_id) ' : ' * ';

        $sql .= 'FROM ' . static::$db_table . ' WHERE read_status = 1';

        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetchAll();
    }


}