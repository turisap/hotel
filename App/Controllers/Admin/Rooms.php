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
use Core\View;
use App\Models\Admin\Room;

class Rooms extends \Core\Controller {


    // this is an action filter which requires admin status to access these pages
    public function before()
    {
        $this->requireAdmin();

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
            foreach ($photos as $key => $value) { // we use $key to assign 'main' property of each photo (virtually main is the first saved one)

                if($value['error'] != 4){  // error when there is no files which is allowed

                    $photo = new Photo($value); // new instance on photo object for each photo with set of properties from FILES array

                    if ($photo->save($room_id, $key)) {  // save using room_id as a foreigner key
                        $photo_errors[] = 'success';   // push false or true in errors array
                    } else {
                        $photo_errors[] = 'fail';
                    }

                    $photos_objects[] = $photo;

                }

            }




        // check if everything is ok or not and make corresponding redirects and flash messages

        if($room_id != false && !in_array('fail', $photo_errors, false)){ // redirect back to the create room page with a message on success

            Flash::addMessage('Room has been created');
            self::redirect('/admin/rooms/create-room');

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

        $is_valid = ! Room::numberExists((string)$_GET['room_number']);

         // get request from profile/edit
        header('Content-type: application/json'); //
        echo json_encode($is_valid); //echo out true or false for ajax


    }


    // renders template for the all rooms page
    public static function allRoomsAction(){

        // first get all rooms from the database
        $sets = Room::findAllRoomsWithPhotos();
        // pass them to the view
        View::renderTemplate('admin/rooms/all_rooms.html', ['sets' => $sets]);

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
        $id = $_POST['picture_id'] ?? false;

        // and delete  a photo based on that id
        if($id){
            if(Photo::delete($id)){
                Flash::addMessage('Photo has been deleted');
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
                print_r($photos);

               /* // if array isn't empty delete all photos
                if(count($photos) > 0){
                    foreach ($photos as $photo){
                        Photo::delete($photo[])
                    }
                }*/
            }



        } else {
            echo '<h1>HUI</h1>' . $id;
        }
    }



}