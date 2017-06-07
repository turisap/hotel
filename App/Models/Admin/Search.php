<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 11:10 AM
 */

namespace App\Models\Admin;


use PDO;
use App\Models\Review;
use DateTime;


abstract class Search extends \Core\Model {

    public static $view_types = ['Cityscape', 'Garden', 'Seaview'];
    public static $class_types = ['Budget', 'Premium', 'Delux', 'Lux'];
    public static $guests_number = ['2 guests', '4 guests', '6 guests'];
    public static $beds_number = ['1 bed', '2 beds', '3 beds'];
    public static $rooms_number = ['1 room', '2 rooms', '3 rooms'];





    // this method returns categories of search on find room page (f.e. if selected search by view it returns 'Seaview', 'Garden' and so on
    public static function findSearchSubcategories($request){


        switch ($request) {

            case "1":
                return 1;
                break;
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

       // change places of category and subcategory
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
        // delete last comma
        $sentence = substr($sentence, 0, -1);

        return $sentence;

    }


    // this method finds rooms by an approximate room name entered by user
    public static function findByRoomName($search_terms){

         // search by whole sentence
        $search_terms = $search_terms ?? false;

        // if keyword checkbox was checked and input wasn't empty
        if( ! empty($search_terms)){

            // first get rid of possible commas (change them with spaces)
            $search_terms = str_replace(',', ' ', $search_terms);
            $search_terms = str_replace(')', ' ', $search_terms);
            $search_terms = str_replace('(', ' ', $search_terms);


            $sql = "SELECT *, MATCH (local_name) AGAINST ('" . $search_terms . "') AS relevance
             FROM rooms WHERE MATCH (local_name) AGAINST ('" . $search_terms . "' IN BOOLEAN MODE) ORDER BY relevance DESC";
            //return $sql;

            $db = static::getDB();

            $stm = $db->prepare($sql);

            $stm->setFetchMode(PDO::FETCH_CLASS, 'App\Models\Admin\Room');

            $stm->execute();

            return $stm->fetchAll();


        }

        return false;
    }


    // sorts all bookings to one room by sort terms form all bookings to one room page (if the second arg set to true it returns all bookings for all rooms)
    public static function sortBookingSearch($param, $all){

        // get all sort params from POST array
        $status    = $param['status']   ?? false;
        $period  = $param['period'] ?? false;
        $sort = $param['order']  ?? false;
        $room_id = $param['room_id'] ?? false;
        $group = $param['group'] ?? false;


        // create corresponding AND parts
        $checkin = date('Y-m-d', (time() + ($period * 24 * 60 * 60))); // how many days were specified since now
        $sort_by = ($sort == 0) ? 'DESC' : 'ASC';

        $sql = 'SELECT * FROM bookings WHERE ';

        // check whether search terms were supplied with status or show all
        $sql .= ($status == 3) ? ' status = 0' : ' status = ' . $status;

        $sql .= ($status == 3) ? '' : ' AND';

        // add room id
        $sql .= ($all == false) ? ' room_id=' . $room_id . ' AND' : '';


        // add remaining parametres (equality mark for today and tomorrow, while less than for other periods)
        $sql .= !($status == 3) ? (' checkin ' . (($period == 0 || $period == 1) ? ' = ' : ' <= ') . ' \'' . $checkin . '\'') : '';

        // add group by room name or checkin if this is search around all bookings in the hotel
        $sql .= ($all && $group) ? ' ORDER BY room_name '. $sort_by : ' ORDER BY checkin ' . $sort_by;


        $db  = static::getDB();
        $stm = $db->prepare($sql);
        $stm->setFetchMode(PDO::FETCH_CLASS, 'App\Models\Admin\Booking');

        $stm->execute();

        return $stm->fetchAll();


    }


    // this method sorts all reviews to one room accordingly with search terms
    public static function sortAllReviewsToOneRoom($room_id, $params){

        // get values from POST array
        $grade = $params['grade'] ?? false;
        $period = $params['period'] ?? false;
        $order = $params['order'] ?? false;

        // create some dates for comparison purposes
        $today = new DateTime();
        $limit_stamp = $today->modify(('-' . strval($period) . ' days'));
        $limit_date = $limit_stamp->format('Y-m-d');

        $sql = 'SELECT reviews.*, bookings.room_name, bookings.title, bookings.first_name, bookings.last_name FROM reviews  LEFT JOIN bookings USING (booking_id) WHERE reviews.room_id = :room_id';

        // add grade parameters
        $sql .= $grade ? (($grade == 1) ? ' AND reviews.overall < 5 ' : (($grade == 2) ? ' AND reviews.overall BETWEEN 5 AND 7' : ' AND reviews.overall >= 7')) : '';

        // add period parameters
        $sql .= $period ? (($period == 1) ? ' AND date_left = \'' . $limit_date . '\'': ' AND date_left >= \'' . $limit_date. '\'') : ' AND date_left = \'' . $limit_date . '\' ';

        // order parameters (for all except today and yesterday)
        $sql .= ($period == 0 || $period == 1) ? '' : ($order ? ' ORDER BY date_left DESC' : ' ORDER BY date_left ASC');

        //return $sql;

        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, '\App\Models\Review');


        $stm->execute();

        return $stm->fetchAll();


    }


    // sorts courses accordingly with search terms
    public static function sortAllCourses($data){

        // get values from POST array
        $course_name = $data['course_name'] ?? false;
        $category_id    = $data['category'] ?? false;
        $order_price = $data['order'] ?? false;

        // if course name was entered
        if($course_name){

            $sql = "SELECT course_id, MATCH (course_name) AGAINST (:course_name) AS relevance
             FROM courses WHERE MATCH (course_name) AGAINST (:course_name IN BOOLEAN MODE) ORDER BY relevance DESC";

            $db  = static::getDB();
            $stm = $db->prepare($sql);
            $stm->bindValue(':course_name', '\'' . $course_name . '\'', PDO::PARAM_STR);

            $stm->execute();

            $results = $stm->fetchAll(PDO::FETCH_ASSOC);


            // results are without category name and picture path due to  we select only from one table
            // so we join some data to results
            return self::getCoursesSets($results);

        } elseif($category_id) {

            $sql = 'SELECT course_id FROM courses WHERE category_id = :category ORDER BY price '. $order_price;

            $db  = static::getDB();
            $stm = $db->prepare($sql);

            $stm->bindValue(':category_id', $category_id, PDO::PARAM_INT);

            $stm->execute();

            $results = $stm->fetchAll(PDO::FETCH_ASSOC);

            return self::getCoursesSets($results);
        }


        return false; // on the absence of data
    }


    //this method just gets sets of courses with photos and category names for search above
    protected static function getCoursesSets($results){

        // results are without category name and picture path due to  we select only from one table
        // so we join some data to results
        $search_results = array();
        foreach($results as $result){

            // based on our found IDs get complete sets of courses with photos and category names
            $search_results[] = Menu::getAllCoursesWithCategoryNamesAndPhotos($result['course_id']);

        }
        return $search_results;

    }































}