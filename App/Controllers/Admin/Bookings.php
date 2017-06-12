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
use App\Models\Review;
use App\Models\User;
use Core\View;
use App\Models\Admin\Room;
use App\Models\Admin\Search;
use App\Pagination;

class Bookings extends \Core\Admin {

    // action filter requires to be admin to access all pages from this controller
    public function before()
    {
        parent::requireAdmin();

    }

    // this method renders template for all bokings page
    public function viewAllBookings(){

        // find all bookings
        $bookings = Booking::findAll();

        View::renderTemplate('admin/bookings/view_all.html', [
            'bookings' => $bookings
        ]);

    }


    // this method renders form to create a booking
    public function createAction(){

        View::renderTemplate('admin/bookings/find_room.html');

    }

    // get request from selectboxes in search room page and renders template with subcategories in the second selectbox
    public function searchCategoriesAction(){

        // get data from post (category)
        $category = $_POST['categories'] ?? false;

        if($category){

            // get subcategories from search model
            $subcategories = Search::findSearchSubcategories($category);
            Flash::addMessage('Please check subcategory');
            View::renderTemplate('Admin/bookings/find_room.html', ['subcategories' => $subcategories, 'category' => $category]);

        } else {
            self::redirect('/admin/bookings/create');
        }
    }

    // process search form on submission (apply button)
    public static function searchRoomAction(){

        // post data from query string
        $category = array_keys($_POST)[0] ?? $_GET['category'] ?? false;
        $subcategory = $_POST[$category] ?? $_GET['subcategory'] ?? false;
        $pets = $_GET['pets'] ?? 0;
        $aircon = $_GET['aircon'] ?? 0;
        $smoking = $_GET['smoking'] ?? 0;
        $children = $_GET['children'] ?? 0;
        $tv = $_GET['tv'] ?? 0;
        // print_r($category);
        //print_r($subcategory);


        // first get data from the POST array or from query string in the case of switching of pages on pagination
        if(!empty($_POST)){
            $data = $_POST;
        } elseif($category && $subcategory){
            $data = [
                $category  => $subcategory,
                'pets'     => $pets,
                'aircon'   => $aircon,
                'smoking'  => $smoking,
                'children' => $children,
                'tv'       => $tv
            ];
        } else {
            $data = false;
        }

        //print_r($data);


        // if there is data from form
        if($data){

            // add pagination
            $count = count(Search::findCustomSearch($data));
            $current_page = $_GET['page'] ?? 1;
            $items_per_page = 2;
            $pagination = new Pagination($current_page, $items_per_page, $count);

            $results = Search::findCustomSearch($data, $items_per_page, $pagination->offset);
            // if search was successful create a sentence about user's search (like 'Your search was rooms with city view and aircon)
            if($results){

                $results_with_photos = array(); // array for found rooms with photos

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true);
                    $bookings = Booking::findAllBookingsToONeRoom($value->id, 3, true);
                    $result = (array)$value;
                    $result['bookings'] = $bookings;
                    $result['upcoming'] = Booking::upcomingBooking($value->id);  // check if there is an upcoming booking
                    $results_with_photos[] = array_merge((array)$result, $photo);

                }

                //print_r($results_with_photos);

               $search_sentence = Search::assemblySearchSentence($data);
                View::renderTemplate('admin/bookings/booking_results.html', [
                    'rooms'        => $results_with_photos,
                    'sentence'     => $search_sentence,
                    'pagination'   => $pagination,
                    'data'         => $data,
                    'category'     => $category,
                    'subcategory'  => $subcategory
                ]);

            } else {

                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/bookings/find_room.html');

            }

        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/bookings/find_room.html');
        }
    }


    // this method processes search request from find by name input
    public function findByRoomName(){

        // first get data from the POST array or from query string in the case of pagination
        $search_terms = $_POST['search_by_name'] ?? $_GET['name'] ?? false;


        if($search_terms){

            // add pagination
            $count = count(Search::findByRoomName($search_terms));
            $current_page = $_GET['page'] ?? 1;
            $items_per_page = 5;
            $pagination = new Pagination($current_page, $items_per_page, $count);


            $results = Search::findByRoomName($search_terms);

            if(!empty($results)){


                // array for keeping results (rooms and their main photos)
                $results_with_photos = array();

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true); // find main photo
                    $bookings = Booking::findAllBookingsToONeRoom($value->id, 3, true); // find only three future bookings
                    $result = (array)$value;
                    $result['bookings'] = $bookings;      // apppend all bookings
                    $result['upcoming'] = Booking::upcomingBooking($value->id);  // check if there is an upcoming booking
                    $results_with_photos[] = array_merge((array)$result, $photo);
                }

                View::renderTemplate('admin/bookings/booking_results.html', [
                    'rooms'         => $results_with_photos,
                    'search'          => $search_terms,
                    'pagination'    => $pagination,
                    'by_room_name'  => 1
                ]);

            } else {
                Flash::addMessage('Nothing has been found', Flash::INFO);
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

            // get averages for all Reviews for the room
            $reviews = Review::getAverageRatingsForARoom($room_id);


            if($room){
                // render template with room on success
                View::renderTemplate('admin/bookings/book_room.html', [
                    'room'     => $room,        // room object
                    'bookings' => $bookings, // and all its bookings
                    'reviews'  => $reviews
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

                Flash::addMessage('Your booking was created successfully');
                // get current user's object
                $user = Authentifiacation::getCurrentUser();
                Mail::send($user->email, 'MyHotelSystem', 'New booking', View::getTemplate('admin/bookings/booking_email.html', [
                    'user' => $user,
                    'booking' => $booking
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
                Flash::addMessage('There was a problem processing your request', Flash::INFO);
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

            } else {
                Flash::addMessage('Cancellation is impossible due to elapsing of the cancellation period', Flash::DANGER);
                self::redirect('/admin/bookings/book-room?id=' . $booking->room_id);
            }


        } else {

            Flash::addMessage('It looks like this room doesn\'t exist' );
            self::redirect('/admin/bookings/create');
        }
    }


    // this method renders particular page
    public function viewAllBookingsToOneRoom(){

        $room_id = $_GET['id'] ?? false;

        if($room_id){

            $bookings = Booking::findAllBookingsToONeRoom($room_id);
            $room = Room::findById($room_id);

            if($bookings){
                View::renderTemplate('admin/bookings/all_bookings_to_a_room.html', [
                    'bookings' => $bookings,
                    'room'  => $room
                ]);
            } else {

                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/bookings/all_bookings_to_a_room.html', [
                    'room'  => $room
                ]);

            }


        } else {
            Flash::addMessage('This room doesn\'t exist', Flash::INFO);
            self::redirect('admin/bookings/create');
        }


    }

    // this method processes searching params from all bookings to one room page
    public function sortAllBookingsToOneRoom(){


        // get sort params from the POST array
        $params = $_POST;
        $room_id = $_POST['room_id'] ?? false;

        //print_r($params);



        // if the 2nd parameter set to true it returns all bookings around hotel, while here only for a room
        $results = Search::sortBookingSearch($params, false);
        $room = Room::findById($room_id);

       // $count =  count($params);
        //echo $count;
        if(!empty($params)){

            if($results){

                //print_r($params);
                View::renderTemplate('admin/bookings/all_bookings_to_a_room.html', [
                    'bookings' => $results,
                    'room'  => $room,
                    'params' => $params
                ]);

            } else {
                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/bookings/all_bookings_to_a_room.html', [
                    'room'  => $room,
                    'params' => $params
                ]);
            }

        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/bookings/all_bookings_to_a_room.html', ['room'  => $room ]);
        }


    }


    // sorts all bookings in hotel accordingly with search terms
    public static function sortAllBookingsAroundHotelAction(){

        $params = $_POST;

        //print_r(Search::sortBookingSearch($params, true));

        if(!empty($params)){

            // if 2nd parameter set to true it returns all bookings for all rooms
            $results = Search::sortBookingSearch($params, true);

           // print_r($results);

            if($results){

                View::renderTemplate('admin/bookings/view_all.html', [
                    'bookings' => $results,
                    'params' => $params
                ]);

            } else {
                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/bookings/view_all.html', [
                    'params' => $params
                ]);
            }
        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/bookings/all_bookings_to_a_room.html');
        }
    }


    // this method renders template for new bookings from link in admin/home page
    public function viewNew(){

        // get array of booking IDs from query string
        $ids = $_GET['booking_ids']['ids'] ?? false;


        // run further code only if IDs are present
        if($ids && !empty($ids)){

            $booking_ids = explode(',', $ids);

            // get rid of duplicate  IDs values ( ine the case when the same booking was made and cancelled)
            $clean_list = array_unique($booking_ids, SORT_NUMERIC);

            $bookings = array();
            foreach ($clean_list as $item) {
                if($item != ''){
                    $bookings[] = Booking::findById($item);
                }
            }

            View::renderTemplate('admin/bookings/view_new.html', ['bookings' => $bookings]);

        } else {
            Flash::addMessage('It looks like there are no such bookings');
            self:: redirect('/admin/home/index');
        }

    }






















}