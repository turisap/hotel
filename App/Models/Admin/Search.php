<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 11:10 AM
 */

namespace App\Models\Admin;


use PDO;


abstract class Search extends \Core\Model {

    public static $view_types = ['sityscape', 'garden', 'seaview'];
    public static $class_types = ['budget', 'premium', 'delux', 'lux'];
    public static $guests_number = ['2 guests', '4 guests', '6 guests'];
    public static $beds_number = ['1 bed', '2 beds', '3 beds'];
    public static $rooms_number = ['1 room', '2 rooms', '3 rooms'];




    // this method returns categories of search on find room page (f.e. if selected search by view it returns 'Seaview', 'Garden' and so on
    public static function findSearchSubcategories($request){


        switch ($request) {
            case "by_class":
                return static::$class_types;
                break;
            case "by_num":
                return static::findAllNumbers();
                break;
            case "by_view":
                return static::$view_types;
                break;
            case "by_guests_num":
                return static::$guests_number;
                break;
            case "by_beds_num":
                return static::$beds_number;
                break;
            case "by_rooms_num":
                return static::$rooms_number;
                break;
            case "all":
                return Room::findAll();
                break;
            default:
                return false;
        }

    }


    public static function findAllNumbers(){

        $sql = 'SELECT room_number FROM rooms';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->setFetchMode(PDO::FETCH_ASSOC);
        $stm->execute();

        return $stm->fetchAll();

    }

}