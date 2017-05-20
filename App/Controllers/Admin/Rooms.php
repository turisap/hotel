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

    // processing create room form
    public function create(){

        $room = new Room($_POST); // create a room object using form data
        $photos = Photo::reArrayFiles($_FILES['photos']);

        $room_id = $room->save();  // save() returns id of last inserted element on success and false on failure

        $photo_errors = array();

        // save all photos which were attached while creating room
        foreach ($photos as $photo) {

            $photo = new Photo($photo); // new instance on photo object for each photo with set of properties from FILES array

            if ($photo->save($room_id)) {  // save using room_id as a foreigner key
                $photo_errors[] = true;   // push false or true in errors array
            } else {
                $photo_errors[] = false;
            }

        }



        if($room_id != false && !in_array(0, $photo_errors, false)){ // redirect back to the create room page with a message on success

            Flash::addMessage('Room has been created');
            self::redirect('/admin/rooms/create-room');

        } elseif ($room_id == false) {

            Flash::addMessage('Please fix all mistakes', Flash::DANGER);
            View::renderTemplate('Admin/rooms/create_room.html', ['room' => $room]);

        } elseif(in_array(0, $photo_errors, false)) {   // render template with errors array on failure

            Flash::addMessage('There were problems uploading images. Please check on rooms page', Flash::DANGER);
            self::redirect('/admin/rooms/all-rooms');

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
        $rooms = Room::findAll();
        // pass them to the view
        View::renderTemplate('admin/rooms/all_rooms.html', ['rooms' => $rooms]);

    }

    // renders page for a particular room
    public function roomAction(){

        // find room by it's id via get request
        $room = Room::findById($_GET['id']);
        // render template and pass the room object
        View::renderTemplate('Admin/rooms/room.html', ['room' => $room]);

    }














    public function test(){
        print_r(Room::numberExists('001'));
}










}