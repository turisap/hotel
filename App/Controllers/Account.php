<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-May-17
 * Time: 2:16 PM
 */

namespace App\Controllers;
use App\Models\User;


class Account extends \Core\Controller {

    // this method validates uniqueness of an email entered to the registration form
    public function validateEmailAction(){

        $is_valid = ! User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null); // check the email in the database,
                                                                                              // get request from profile/edit
        header('Content-type: application/json'); //
        echo json_encode($is_valid); //echo out true or false for ajax


}

}