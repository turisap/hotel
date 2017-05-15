<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 7:37 PM
 */

namespace App;



class Flash {

    // constants to style messages
    const SUCCESS = 'success';
    const DANGER  = 'danger';
    const INFO    = 'info';

    // this method adds a message to a session var
    public static function addMessage($body, $type = 'success'){
        // first check whether session var is set
        if( ! isset($_SESSION['flash_messages'])){
            $_SESSION['flash_messages'] = [];
            $_SESSION['flash_messages'] [] = [
                'body' => $body,
                'type' => $type
            ];;
        }
    }

    // this method returns array of messages
    public static function getFlashMessage(){
        // return it only if it was set
        if(isset($_SESSION['flash_messages'])){
            $message = $_SESSION['flash_messages'];
            // unset session to avoid message staying forever
            unset($_SESSION['flash_messages']);
            return $message;
        }
    }

}