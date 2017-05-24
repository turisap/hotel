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

            $string = true; // set it to true in order to use further to decide whether to bind value (view or class search)

            $subcategory = $data[$category]; // like data['view']

            // add catigory and subcategory to sql statement
            $where_list[] = $category . ' = :subcategory'; // combine array with for where clause

            // but these categories contains strings and numbers (which we need)
        } elseif($category == 'room_number' || $category == 'num_guests' || $category == 'num_beds' || $category == 'num_rooms'){

            $string = false;  // set it to false if we obtain int from room number or other string( we don't need to bind values)

            preg_match('!\d+!', $data[$category], $matches);
            $subcategory = $matches[0];
            // add catigory and subcategory to sql statement
            $where_list[] = $category . ' = ' . $subcategory;    // combine array with for where clause

        }





        $pets     = $data['pets'] ?? false;
        $aircon   = $data['aircon'] ?? false;
        $smoking  = $data['smoking'] ?? false;
        $children = $data['children'] ?? false;
        $tv       = $data['tv'] ?? false;

        if( ! ($category == 'room_number') ){

            !$pets ?: $where_list[] = ' pets = 1';    // combine array with for where clause
            !$aircon ?: $where_list[] = ' aircon = 1';
            !$smoking ?: $where_list[] = ' smoking = 1';
            !$children ?: $where_list[] = ' children = 1';
            !$tv ?: $where_list[] .= ' tv = 1 ';
        }

        $where_clause = implode(' AND ', $where_list); // implode collected array with AND clause
        $sql .= $where_clause;



        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $string = (isset($string) && $string) ? true : false; // if it was set and is true make it true and false otherwise

        if($string){
            $stm->bindValue(':subcategory', $subcategory, PDO::PARAM_STR);
        }

        //return $sql;

        //return $sql;
        $stm->setFetchMode(PDO::FETCH_CLASS, '\App\Models\Admin\Room');

        $stm->execute();

        return $stm->fetchAll();

    }


    // this method creates a search sentence_list based on
    public static function assemblySearchsentence($data){

        // first get a category from the POST array
        $category = array_keys($data)[0];
        // then get the subcategory
        $subcategory = $data[$category];

        // data from checkboxes
        $pets = $data['pets'] ?? false;
        $aircon = $data['aircon'] ?? false;
        $smoking = $data['smoking'] ?? false;
        $children = $data['children'] ?? false;
        $tv = $data['tv'] ?? false;


        // array for sentence_list elements
        $sentence_list = array();


        switch ($category) {
            case "class":
                $sentence_list[] = 'You are looking for accommodation with';
                $sentence_list[] = 'class,';
                break;
            case "room_number":
                $sentence_list[] = 'You are looking for accommodation by its number';
                break;
            case "view":
                $sentence_list[] = 'You are looking for accommodation with';
                $sentence_list[] = 'view,';
                break;
            case "num_guests":
                $sentence_list[] = 'You are looking for accommodation with maximum number of';
                break;
            case "num_beds":
                $sentence_list[] = 'You are looking for accommodation with';
                break;
            case "num_rooms":
                $sentence_list[] = 'You are looking for accommodation with';
                break;
            default:
                return false;
        }

                switch ($subcategory) {
                    case "Budget":
                        $sentence_list[] = 'budget';
                        break;
                    case "Premium":
                        $sentence_list[] = 'premium';
                        break;
                    case "Lux":
                        $sentence_list[] = 'lux';
                        break;
                    case "Delux":
                        $sentence_list[] = 'delux';
                        break;
                    case "Cityscape":
                        $sentence_list[] = 'a cityscape';
                        break;
                    case "Garden":
                        $sentence_list[] = 'a garden';
                        break;
                    case "Seaview":
                        $sentence_list[] = 'a sea';
                        break;
                    case "2 guests":
                        $sentence_list[] = '2 guests,';
                        break;
                    case "4 guests":
                        $sentence_list[] = '4 guests,';
                        break;
                    case "6 guests":
                        $sentence_list[] = '6 guests,';
                        break;
                    case "1 bed":
                        $sentence_list[] = '2 guests,';
                        break;
                    case "2 beds":
                        $sentence_list[] = '2 beds,';
                        break;
                    case "6 beds":
                        $sentence_list[] = '3 beds,';
                        break;
                    case "1 room":
                        $sentence_list[] = '1 beds,';
                        break;
                    case "2 rooms":
                        $sentence_list[] = '2 beds,';
                        break;
                    case "3 rooms":
                        $sentence_list[] = '3 beds,';
                        break;

                    default:
                        return false;
                }

       //
        if(count($sentence_list) == 3){
            $out = array_splice($sentence_list, 1, 1);
            $sentence_list[] = $out[0];
        }


        !$pets ?: $sentence_list[] = 'with pets allowed,';
        !$aircon ?: $sentence_list[] = 'with aircon,';
        !$smoking ?: $sentence_list[] = 'with smoking allowed,';
        !$children ?: $sentence_list[] = 'with facilities for children,';
        !$tv ?: $sentence_list[] = 'and with TV set';

        
        // create a sentence_list out of the array
        $sentence = implode(' ', $sentence_list);
        $sentence = substr($sentence, 0, -1);

        return $sentence;

    }








}