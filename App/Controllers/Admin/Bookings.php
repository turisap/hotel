<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 9:44 AM
 */

namespace App\Controllers\Admin;


use App\Flash;
use App\Models\Admin\Booking;
use App\Models\Admin\Photo;
use Core\View;
use App\Models\Admin\Room;
use \App\Models\Admin\Search;

class Bookings extends \Core\Controller {

    // action filter requires to be admin to access all pages from this controller
    public function before()
    {
        //parent::before();
        $this->requireAdmin();
    }


    // this method renders form to create a booking
    public function createAction(){

        View::renderTemplate('admin/bookings/find_room.html');

    }

    // get request from selectboxes in search room page and renders template with subcategories in the second selectbox
    public function searchCategoriesAction(){

        // get data from post (category)
        $category = $_POST['categories'] ?? false;

        //print_r($category);


        if($category){

            // get subcategories from search model
            $subcategories = Search::findSearchSubcategories($category);
            Flash::addMessage('Please check subcategory');
            View::renderTemplate('Admin/bookings/find_room.html', ['subcategories' => $subcategories, 'category' => $category]);

        }
    }

    // process search form on submission (apply button)
    public static function searchRoomsAction(){

        // first get data from the POST array
        $data = $_POST ?? false;

        // if there is data from form
        if($data){

            $results = Search::findCustomSearch($data);
            // if search was successful create a sentence about user's search (like 'Your search was rooms with city view and aircon)
            if($results){

                $results_with_photos = array(); // array for found rooms with photos

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true);
                    $results_with_photos[] = array_merge((array)$value, $photo);
                }

               $search_sentence = Search::assemblySearchSentence($data);
                View::renderTemplate('admin/bookings/find_room.html', [
                    'rooms' => $results_with_photos,
                    'sentence' => $search_sentence
                ]);

            }

        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/bookings/find_room.html');
        }
    }


    // this method processes search request from find by name input
    public function findByRoomName(){

        // first get data from the POST array
        $search_terms = $_POST['search_by_name'] ?? false;

        //var_dump(Search::findByRoomName($search_terms));


        if($search_terms){

            $results = Search::findByRoomName($search_terms);

            if($results && !empty($results)){


                // array for keeping results (rooms and their main photos)
                $results_with_photos = array();

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true);
                    $results_with_photos[] = array_merge((array)$value, $photo);
                }

                View::renderTemplate('admin/bookings/find_room.html', ['rooms' => $results_with_photos]);

            } else {
                Flash::addMessage('Nothing was found', Flash::INFO);
                self::redirect('/admin/bookings/findByRoomName');
            }

        } else {

            // redirect back on failure with a message
            Flash::addMessage('There was a problem processing your requests, please try again');
            View::renderTemplate('admin/bookings/find_room.html', ['search' => $search_terms]);

        }


    }

    public function bookRoomAction(){

        //get room's id from query string
        $room_id = $_GET['id'] ?? false;

        if($room_id){

            // find a room based on that id
            $room = Room::findById($room_id);

            if($room){
                // render template with room on success
                View::renderTemplate('admin/bookings/book_room.html', [
                    'room' => $room  // room object
                ]);

            } else {
                Flash::addMessage('There is no such a room', Flash::INFO);
                $this->redirect('/admin/bookings/create');
            }

        } else {
            Flash::addMessage('There was a problem processing your request, try again', Flash::INFO);
            $this->redirect('/admin/bookings/create');
        }


    }

    // process book room form and crate a booking
    public function newBooking(){

        // get data from POST array
        $data = $_POST ?? false;
        //print_r($data);

        if($data){

            // create a new Booking object based on POST data

            $booking = new Booking($data);

            // call Booking model's method to create a record in the database and proceed on success
            if($booking->newBooking()){

                echo '<h1>TROLOLO</h1>';

            }
        }


    }




















}