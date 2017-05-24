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

    public static $view_types = ['Cityscape', 'Garden', 'Seaview'];
    public static $class_types = ['Budget', 'Premium', 'Delux', 'Lux'];
    public static $guests_number = ['2 guests', '4 guests', '6 guests'];
    public static $beds_number = ['1 bed', '2 beds', '3 beds'];
    public static $rooms_number = ['1 room', '2 rooms', '3 rooms'];




    // this method returns categories of search on find room page (f.e. if selected search by view it returns 'Seaview', 'Garden' and so on
    public static function findSearchSubcategories($request){


        switch ($request) {
            case "class":
                return static::$class_types;
                break;
            case "room_number":
                return static::findAllNumbersWithNames();
                break;
            case "view":
                return static::$view_types;
                break;
            case "num_guests":
                return static::$guests_number;
                break;
            case "num_beds":
                return static::$beds_number;
                break;
            case "num_rooms":
                return static::$rooms_number;
                break;
            default:
                return false;
        }

    }


    // gets numbers of all rooms with their local_names
    public static function findAllNumbersWithNames(){

        $sql = 'SELECT room_number, local_name FROM rooms';
        $db  = static::getDB();

        $stm = $db->prepare($sql);

        $stm->execute();

        $array = $stm->fetchAll();

        if($array){

            // rearray to a more comfortable form
            return static::rearrayNumbers($array);

        }

        return false;


    }

    // rearrays search result to a more convenient one-dimensional array
    public static function rearrayNumbers($arrays){

        // array for collecting names and numbers
        $numbers_with_names = array();

        foreach ($arrays as $array){

            $numbers_with_names[] =  $array['room_number'] . '  ' . '(' . $array['local_name'] . ')';

        }

        return $numbers_with_names;
    }


    // finds room according to the data from form
    public static function findCustomSearch($data){

        $sql = 'SELECT * FROM rooms WHERE ';

        $category = array_keys($data)[0];   // here we get a category from the post array (this is a key)

        // subcategory is the first element of the post array (but it also may contain strings)
        if($category == 'class' || $category == 'view'){ // these categories contain only strings

            $string = true;

            $subcategory = $data[$category];

            // add catigory and subcategory to sql statement
            $where_list[] = $category . ' = :subcategory';

            // but these categories contains strings and numbers (which we need)
        } elseif($category == 'room_number' || $category == 'num_guests' || $category == 'num_beds' || $category == 'num_rooms'){

            $string = false;

            preg_match('!\d+!', $data[$category], $matches);
            $subcategory = $matches[0];
            // add catigory and subcategory to sql statement
            $where_list[] = $category . ' = ' . $subcategory;

        }





        $pets     = $data['pets'] ?? false;
        $aircon   = $data['aircon'] ?? false;
        $smoking  = $data['smoking'] ?? false;
        $children = $data['children'] ?? false;
        $tv       = $data['tv'] ?? false;

        if( ! ($category == 'room_number') ){

            !$pets ?: $where_list[] = ' pets = 1';
            !$aircon ?: $where_list[] = ' aircon = 1';
            !$smoking ?: $where_list[] = ' smoking = 1';
            !$children ?: $where_list[] = ' children = 1';
            !$tv ?: $where_list[] .= ' tv = 1 ';
        }

        $where_clause = implode(' AND ', $where_list);
        $sql .= $where_clause;



        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $string = (isset($string) && $string) ? true : false;

        if($string){
            $stm->bindValue(':subcategory', $subcategory, PDO::PARAM_STR);
        }

        //return $sql;

        //return $sql;
        $stm->setFetchMode(PDO::FETCH_CLASS, '\App\Models\Admin\Room');

        $stm->execute();

        return $stm->fetchAll();

    }








}