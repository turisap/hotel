<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 19-May-17
 * Time: 11:55 AM
 */

namespace App\Controllers\Admin;


use App\Flash;
use App\Models\Admin\Photo;
use App\Pagination;
use Core\View;
use App\Models\Admin\Room;
use App\Models\Admin\Search;
use App\Models\Admin\Booking;


class Rooms extends \Core\Admin {


    // this is an action filter which requires admin status to access these pages / mooved to Core/Admin
    public function before()
    {
        parent::requireAdmin();

    }

    // renders create room page
    public function createRoomAction(){
        View::renderTemplate('Admin/Rooms/create_room.html');
    }

    // this method creates a room, saving records in photos and rooms tables
    public function create(){

        $room = new Room($_POST); // create a room object using form data
        $photos = Photo::reArrayFiles($_FILES['photos']);

        $room_id = $room->save();  // save() returns id of last inserted element on success and false on failure

        $photo_errors = array();   // errors array
        $photos_objects = array();        // array for photos objects


            // save all photos which were attached while creating room
            $i = 1; // use it as counter instead of $key due to restrictions in Photo save()
            foreach ($photos as $key => $value) { // we use $key to assign 'main' property of each photo (virtually main is the first saved one)

                if($value['error'] != 4){  // error when there is no files which is allowed

                    $photo = new Photo($value); // new instance on photo object for each photo with set of properties from FILES array

                    if ($photo->save($room_id, $i)) {  // save using room_id as a foreigner key
                        $photo_errors[] = 'success';   // push false or true in errors array
                        $i++;
                    } else {
                        $photo_errors[] = 'fail';
                    }

                    $photos_objects[] = $photo;

                }

            }




        // check if everything is ok or not and make corresponding redirects and flash messages

        if($room_id != false && !in_array('fail', $photo_errors, false)){ // redirect back to the create room page with a message on success

            Flash::addMessage('Room has been created');
            self::redirect('/admin/rooms/all-rooms');

        } elseif ($room_id == false) {

            Flash::addMessage('Please fix all mistakes', Flash::DANGER);
            View::renderTemplate('Admin/rooms/create_room.html', ['room' => $room]);

        } elseif(in_array('fail', $photo_errors, true)) {   // render template with errors array on failure

            Room::delete($room_id);                                           // delete newly create room due to errors uploading files
            Flash::addMessage('There were problems uploading images.', Flash::DANGER);
            View::renderTemplate('Admin/rooms/create_room.html', ['room' => $room, 'photos' => $photos_objects]);

        }

    }



    // this method validates uniqueness of an room number entered to the create room form
    public static function validateRoom(){

        $is_valid = ! Room::numberExists((int)$_GET['room_number'], $_GET['ignore_id'] ?? null);

         // get request from profile/edit
        header('Content-type: application/json'); //
        echo json_encode($is_valid); //echo out true or false for ajax


    }


    // renders template for the all rooms page
    public static function allRoomsAction(){

        // add pagination
        $current_page = $_GET['page'] ?? false;
        $items_per_page = 10;
        $rooms_count = count(Room::findAll());

        $pagination = new Pagination($current_page, $items_per_page, $rooms_count);

        // first get all rooms from the database (two parameters are limit and offset for pagination)
        $sets = Room::findAllRoomsWithPhotos($pagination->items_per_page, $pagination->offset);

        // pass them to the view
        View::renderTemplate('admin/rooms/all_rooms.html', [
            'rooms'     => $sets,
            'pagination' => $pagination
        ]);

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
            View::renderTemplate('Admin/rooms/all_rooms.html', ['subcategories' => $subcategories, 'category' => $category]);

        } else {
            self::redirect('/admin/rooms/all');
        }
    }

    // process search form on submission (apply button)
    public static function searchRoomAction(){


        // post data from query string
        $category = array_keys($_POST)[0] ?? $_GET['category'] ?? false;
        $subcategory = $_POST[$category] ?? $_GET['subcategory'] ?? false;
        $pets = $_POST['pets'] ?? $_GET['pets'] ?? 0;
        $aircon = $_POST['aircon'] ?? $_GET['aircon'] ?? 0;
        $smoking = $_POST['smoking'] ?? $_GET['smoking'] ?? 0;
        $children = $_POST['children'] ?? $_GET['children'] ?? 0;
        $tv = $_POST['tv'] ?? $_GET['tv'] ?? 0;



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


        // if there is data from form or query string
        if($data){

            // count all rooms found for search
            $count = count(Search::findCustomSearch($data));

            // add pagination
            $current_page = $_GET['page'] ?? 1;
            $items_per_page = 2;
            $pagination = new Pagination($current_page, $items_per_page, $count);

            $results = Search::findCustomSearch($data, $items_per_page, $pagination->offset);


            // if search was successful create a sentence about user's search (like 'Your search was rooms with city view and aircon)
            if($results){

                $results_with_photos = array(); // array for found rooms with photos

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){

                    $result = (array)$value;
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true); // find only main photo
                    $result['upcoming'] = Booking::upcomingBooking($value->id);   // check if there is an upcoming booking
                    $results_with_photos[] = array_merge($result, (array)$photo);
                }


                $search_sentence = Search::assemblySearchSentence($data, $count);

                View::renderTemplate('admin/rooms/search_results.html', [
                    'rooms'       => $results_with_photos,
                    'sentence'    => $search_sentence,
                    'view_all'    => 1,
                    'pagination'  => $pagination,
                    'data'        => $data,
                    'category'    => $category,        // pass category of search
                    'subcategory' => $subcategory      // pass subcategory to the view
                ]);

            } else {

                Flash::addMessage('Nothing has been found', Flash::INFO);
                View::renderTemplate('admin/rooms/all_rooms.html');

            }

        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/rooms/all_rooms.html');
        }
    }


    // this method processes search request from find by name input
    public function findByRoomName(){

        // first get data from the POST array or query string in he case of changing pages by pagination
        $search_terms = $_POST['search_by_name'] ?? $_GET['name'] ?? false;

        if($search_terms){

            // add pagination
            $count = count(Search::findByRoomName($search_terms));
            $current_page = $_GET['page'] ?? 1;
            $items_per_page = 5;
            $pagination = new Pagination($current_page, $items_per_page, $count);

            $results = Search::findByRoomName($search_terms, $items_per_page, $pagination->offset);

            if(!empty($results)){


                // array for keeping results (rooms and their main photos)
                $results_with_photos = array();

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){

                    $photo = Photo::findAllPhotosToONeRoom($value->id, true); // find only main photo
                    $result = (array)$value;
                    $result['upcoming'] = Booking::upcomingBooking($value->id);  // check if there is an upcoming booking
                    $results_with_photos[] = array_merge($result, (array)$photo);
                }

               $sentence = $count . ' results found';

                View::renderTemplate('admin/rooms/search_results.html', [
                    'rooms'        => $results_with_photos,
                    'sentence'     => $sentence,
                    'view_all'     => 1,
                    'search'       => $search_terms,
                    'pagination'   => $pagination,
                    'by_room_name' => 1
                ]);

            } else {
                Flash::addMessage('Nothing has been found', Flash::INFO);
                self::redirect('/admin/admin/all-rooms');
            }

        } else {

            // redirect back on failure with a message
            Flash::addMessage('There was a problem processing your requests, please try again');
            View::renderTemplate('admin/rooms/all_rooms.html', ['search' => $search_terms]);

        }


    }






    // renders page for a particular room
    public function roomAction(){

        // find room by it's id via get request
        $room_id = $_GET['id'] ?? false;

        if($room_id){

            // find a room
            $room = Room::findById($room_id);

            if($room){
                // find all pictures for this room
                $pictures = Photo::findAllPhotosToONeRoom($room->id, false);
                // render template and pass the room object
                View::renderTemplate('Admin/rooms/check_room.html', ['room' => $room, 'pictures' => $pictures, 'room_id' => $room_id]);

            } else { // no room found

                Flash::addMessage('such a room does\'t exist', Flash::INFO);
                self::redirect('/admin/rooms/all-rooms');

            }


        } else {
            Flash::addMessage('there was an error accessing the room', Flash::DANGER);
            self::redirect('/admin/rooms/all-rooms');
        }

    }

    // sets picture as a main one via ajax request from the room page
    public static function setMainPicture(){

        // assing data from ajax request to vars
        $picture_id = $_POST['picture_id'] ?? false;
        $room_id = $_POST['room_id'] ?? false;

        Photo::setPictureAsMain($picture_id, $room_id);

    }

    // this method adds photos to a room's  profile on edit page
    public static function addPhotos(){

        // first get room id from query string from form action property
        $room_id = $_GET['id'] ?? false;

        // if FILES array is emply, redirect back
        if($_FILES['photos']['name'][0] == ''){
            self::redirect('/admin/rooms/room?id=' . $room_id);
        }


        // get pictures data via POST array
        $pictures = Photo::reArrayFiles($_FILES['photos']);

        // and create picture objects out of them and push them into an array
        $pictures_objects = array();
        foreach($pictures as $picture){
            $photo = new Photo($picture);
            $pictures_objects[] = $photo;
        }

        //print_r(Photo::addPhotos($room_id, $pictures_objects));


       if(Photo::addPhotos($room_id, $pictures_objects)){

            // redirect back to the original page on success
            Flash::addMessage('Photos were added successfully');
            self::redirect('/admin/rooms/room?id=' . $room_id); // query string

        } else {

           // inform user there were problems on upload
           Flash::addMessage('There were problems on upload, please check photos in a room page');
           self::redirect('/admin/rooms/room?id=' . $room_id); // query string

       }

    }

    // deletes a photo from view room page
    public static function deletePhoto(){

        // first get id from the query string
        $id = $_GET['picture_id'] ?? false;
        $room_id = $_GET['room_id'] ?? false;

        // and delete  a photo based on that id
        if($id){

            $photo = Photo::findById($id);

            if(Photo::delete($id) && Photo::unlinkImages($photo->name, 0)){
                Flash::addMessage('Photo has been deleted');
                self::redirect('/admin/rooms/room?id=' . $room_id);
            }
        }

    }

    public static function deleteRoom(){

        $room_id = $_GET['id'];

        if($room_id){

            // get a room object
            $room = Room::findById($room_id);

            // get all photos to it
            if($room){

                $photos = Photo::findAllPhotosToONeRoom($room_id, false);


                // delete room (ALL RECORDS ABOUT PHOTOS ARE DELETED AUTOMATICALLY)
                Room::delete($room_id);

                // delete images from uploads folder (record from the database deleted automatically)
                if(count($photos) > 0){ // if there are images

                    foreach ($photos as $photo) {
                        Photo::unlinkImages($photo['name'], 0);
                        Photo::delete($photo['id']);
                    }

                }

                Flash::addMessage('Room has been deleted');
                self::redirect('/admin/rooms/all-rooms');


            }

        } else {
            Flash::addMessage('This room probably doesn\'t exist', Flash::INFO);
            self::redirect('/admin/rooms/all-rooms');
        }
    }


    // this method renders edit room page
    public function editRoomAction(){

        // get room's id from get request first
        $room_id = $_GET['id'] ?? false;

        if($room_id){

            // find a room based on that id
            $room = Room::findById($room_id);
            // and all pictures to that room
            $pictures = Photo::findAllPhotosToONeRoom($room_id, false);

            // render the page and pass room object to it
            View::renderTemplate('admin/rooms/edit_room.html', ['room' => $room, 'pictures' => $pictures]);

        } else { // if there is no room with such an id
            Flash::addMessage('This room probably doesn\'t exist', Flash::INFO);
            self::redirect('/admin/rooms/all-rooms');
        }


    }


    public function updateRoom(){

        // first get data from the form
        $data    = $_POST ?? false;
        $room_id = $_GET['id'] ?? false;

        //print_r($data);



        if($data){

            if($room_id){

                $room = new Room();

                // redirect to check room page on successful redirect
                if($room->updateRoom($data, $room_id)){

                    Flash::addMessage('Changes were saved');
                    self::redirect('/admin/rooms/room?id='.$room_id);

                } else {
                    // if there is no room id (or wrong one) in query string
                    Flash::addMessage('Unsuccessful saving', Flash::DANGER);
                    self::redirect('/admin/rooms/edroom?id='.$room_id);
                }

            } else {

                // if there is no room id in query string
                Flash::addMessage('There was probably no such a room', Flash::INFO);
                self::redirect('/admin/rooms/edit-room?id='.$room_id);

            }

        } else {

            // if no data is given througn the POST array
            Flash::addMessage('There were problems access entered data', Flash::INFO);
            self::redirect('/admin/rooms/edit-room?id='.$room_id);

        }

    }


}