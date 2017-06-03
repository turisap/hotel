<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 02-Jun-17
 * Time: 9:13 PM
 */

namespace App\Controllers\Admin;


use App\Flash;
use App\Models\Admin\Room;
use App\Models\Review;
use Core\View;
use App\Models\Admin\Search;

class Reviews extends \Core\Admin {

    public function before()
    {
        parent::before(); // TODO: Change the autogenerated stub
    }



    // this method renders page with all reviews to a particular room
    public function allReviewsToOneRoomAction(){

        // first get room_id from the query string
        $room_id = $_GET['room_id'] ?? false;

        // render the page
        if($room_id){

            $reviews = Review::findAllReviewsToOneroom($room_id);
            View::renderTemplate('/admin/reviews/all_reviews.html', ['reviews' => $reviews]);

        } else {
            Flash::addMessage('It looks like there is no such a room, please try to find another');
            self::redirect('/admin/bookings/create');

        }
    }


    // processin form submission on sorting reviews
    public function sortAllReviewsToOneRoomAction(){

        $room_id = $_GET['id'] ?? false;
        $params  = $_POST;

        if($room_id){

            $reviews = Search::sortAllReviewsToOneRoom($room_id, $params);

            //var_dump($reviews);

            if(count($reviews) > 0){

                View::renderTemplate('admin/reviews/all_reviews.html', [
                    'reviews' => $reviews,
                    'params'  => $params
                ]);
                Flash::addMessage('Your results', Flash::INFO);

            } else {

                $room = Room::findById($room_id);
                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/reviews/all_reviews.html', [
                    'room' => $room,
                    'params' => $params
                ]);

            }

        } else {

            Flash::addMessage('It looks like this room doesn\'t exists', Flash::INFO);
            self::redirect('/admin/bookings/create');

        }

    }

}