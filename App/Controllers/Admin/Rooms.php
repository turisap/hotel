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
        $room = Room::findById($_GET['id']);

        // find all pictures for this room
        //$pictures = Photo::

        // render template and pass the room object
        View::renderTemplate('Admin/rooms/edit_room.html', ['room' => $room]);

    }



}