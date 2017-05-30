<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 9:44 AM
 */

namespace App\Controllers\Admin;


use App\Authentifiacation;
use App\Config;
use App\Flash;
use App\Mail;
use App\Models\Admin\Booking;
use App\Models\Admin\Photo;
use App\Models\User;
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

        } else {
            self::redirect('admin/bookings/create');
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


        if($search_terms){

            $results = Search::findByRoomName($search_terms);

            if(!empty($results)){


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
                self::redirect('/admin/bookings/search-room');
            }

        } else {

            // redirect back on failure with a message
            Flash::addMessage('There was a problem processing your requests, please try again');
            View::renderTemplate('admin/bookings/find_room.html', ['search' => $search_terms]);

        }


    }

    // renders page for a room page (with bookings)
    public function bookRoomAction(){

        //get room's id from query string
        $room_id = $_GET['id'] ?? false;

        if($room_id){

            // check status of a booking (1 -upcoming, 0-past, 2 - canceled) and set status to past if check in date in the past
            Booking::checkAndChangeAllBookingsStatus();

            // find a room based on that id
            $room = Room::findById($room_id);
            // and all bookings to that room
            $bookings = Booking::findAllBookingsToONeRoom($room_id);

            if($room){
                // render template with room on success
                View::renderTemplate('admin/bookings/book_room.html', [
                    'room' => $room,        // room object
                    'bookings' => $bookings // and all its bookings
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
        // extract room id in order to pass it to the view in the case of error
        $room_id = $_POST['room_id'] ?? false;


        if(!empty($data)){

            // create a new Booking object based on POST data
            $booking = new Booking($data);

            // and find respective room
            $room = Room::findById($room_id);

            // and all bookings to that room
            $bookings = Booking::findAllBookingsToONeRoom($room_id);

            // call Booking model's method to create a record in the database and proceed on success
            if($booking->newBooking($room->local_name)){

                // get current user's object
                $user = Authentifiacation::getCurrentUser();
                Mail::send($user->email, 'MyHotelSystem', 'New booking', View::getTemplate('admin/bookings/booking_email.html', [
                    'user' => $user,
                    'booking' => $booking,
                    'site_name' => Config::SITE_NAME
                ]));

                // and all bookings to that room
                $bookings = Booking::findAllBookingsToONeRoom($room_id);
                View::renderTemplate('admin/bookings/book_room.html', ['bookings' => $bookings, 'room' => $room]);

            } else {

                if($room){

                    Flash::addMessage('Please fix all errors', Flash::INFO);
                    View::renderTemplate('admin/bookings/book_room.html', [
                        'booking' => $booking,
                        'room'    => $room,
                        'bookings' => $bookings
                    ]);

                } else {

                    $this->redirect('/admin/bookings/create');

                }

            }
        }


    }


    // this function renders page for a particular booking
    public function checkBookingAction(){

        // first get booking's id from the query string
        $booking_id = $_GET['id'] ?? false;

        if($booking_id){

            // check status of a booking (1 -upcoming, 0-past, 2 - canceled) and set status to past if check in date in the past
            Booking::checkAndChangeAllBookingsStatus();
            // find booking object
            $booking = Booking::findById($booking_id);

            if($booking){
                // render template
                View::renderTemplate('admin/bookings/check_booking.html', ['booking' => $booking]);
            } else {
                self::redirect('/admin/bookings/book-room?id='. $booking_id);
            }

        }


    }


    // this method processes the page with booking deletion
    public static function cancelBookingAction(){

        $booking_id = $_GET['booking_id'] ?? false;
        $room_id    = $_GET['room_id'] ?? false;


        if($booking_id){

            // get that booking object
            $booking = Booking::findById($booking_id);

            if($booking){

                View::renderTemplate('admin/bookings/cancel_booking.html', [
                    'booking' => $booking,
                    'room_id'   => $room_id
                ]);

            } else {
                // redirect back to the room page on failure
                Flash::addMessage('It looks like this page doesn\'t exist', Flash::INFO);
                self::redirect('/admin/bookings/book-room?id=' . $room_id);
            }


        } else {
            // redirect back to the room page on failure
            Flash::addMessage('It looks like this page doesn\'t exist', Flash::INFO);
            self::redirect('/admin/bookings/book-room?id=' . $room_id);
        }
    }

    // processes request of canceling booking
    public function cancel(){

        $booking_id = $_POST['booking_id'] ?? false;
        $message = $_POST['cancel_booking'] ?? false;
        $booking = Booking::findById($booking_id);



        if($booking){

            // cancel booking
            if(Booking::cancelBooking($booking_id, true)){

                // send email to the user
                $user = User::findById($booking->user_id);
                Mail::send($user->email, 'MyhotelSystem', 'Booking cancelation', $message);

                // and redirect
                Flash::addMessage('Booking was successfully canceled');
                self::redirect('/admin/bookings/book-room?id=' . $booking->room_id);

            }


        } else {

            Flash::addMessage('It looks like this booking doesn\'t exist');
            self::redirect('/admin/bookings/create');
        }
    }


    // this method renders particular page
    public function viewAllBookingsToOneRoom(){

        $room_id = $_GET['id'] ?? false;

        if($room_id){

            $bookings = Booking::findAllBookingsToONeRoom($room_id);
            if($bookings){
                View::renderTemplate('admin/bookings/all_booking_to_room.html', ['bookings' => $bookings]);
            }


        } else {
            Flash::addMessage('This room doesn\'t exist', Flash::INFO);
            self::redirect('admin/bookings/create');
        }


    }
























}