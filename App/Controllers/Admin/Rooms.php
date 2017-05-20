<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 19-May-17
 * Time: 11:55 AM
 */

namespace App\Controllers\Admin;


use App\Flash;
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

        $room = new Room($_POST, $_FILES); // create a room object using form data

        if($room->save()){ // redirect back to the create room page with a message on success

            Flash::addMessage('Room has been created');
            self::redirect('/admin/rooms/create-room');

        } else {        // render template with errors array on failure

            Flash::addMessage('Please fix all mistakes', Flash::DANGER);
            View::renderTemplate('Admin/rooms/create_room.html', ['room' => $room]);

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