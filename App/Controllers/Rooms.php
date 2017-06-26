<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-Jun-17
 * Time: 2:21 PM
 */

namespace App\Controllers;


use App\Models\Admin\Room;
use App\Pagination;
use Core\View;
use App\Models\Admin\Search;

class Rooms extends \Core\Controller {

    public function before()
    {
        parent::before(); // TODO: Change the autogenerated stub
    }


    // search by dates or by dates and num_guests and smoking
    public function searchResultsAction(){

        //print_r($_POST);

        $checkin  = $_POST['checkin'] ?? $_GET['checkin'] ?? false;
        $checkout = $_POST['checkout'] ?? $_GET['checkout'] ?? false;
        $smoking  = $_POST['smoking'] ?? $_GET['smoking'] ?? false;
        $guests   = $_POST['num_guests'] ?? $_GET['num_guests'] ?? false;

        if($checkin && $checkout){

            $count = count(Search::findRoomsByDates($checkin, $checkout, $smoking, $guests));
            $items_per_page = 5;
            $current_page = $_GET['page'] ?? 1;

            $pagination = new Pagination($current_page, $items_per_page, $count);

            $rooms = Search::findRoomsByDates($checkin, $checkout, $smoking, $guests);
            $offset = ($pagination->offset && ($pagination->offset > 0)) ? $pagination->offset : 0;
            $rooms = array_slice($rooms, $offset, $items_per_page);

            View::renderTemplate('rooms/search_results.html', [
                'rooms'      => $rooms,
                'pagination' => $pagination,
                'checkin'    => $checkin,
                'checkout'   => $checkout,
                'smoking'    => $smoking,
                'num_guests' => $guests
            ]);


        }

    }

    public function prebookRoomAction(){

        $checkin = $_GET['checkin']   ?? false;
        $checkout = $_GET['checkout'] ?? false;
        $room_id  = $_GET['id'] ?? false;

        if($room_id){
            $room = Room::findById($room_id);
        }

        View::renderTemplate('rooms/prebook_room.html', [
            'checkin'  => $checkin,
            'checkout' => $checkout,
            'room'     => $room
        ]);
    }


}