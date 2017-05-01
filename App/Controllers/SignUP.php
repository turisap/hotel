<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/29/2017
 * Time: 8:47 PM
 */

namespace App\Controllers;
use \Core\View;
use App\Models\User;


class SignUP extends \Core\Controller
{

    public function newAction(){
        View::renderTemplate('SignUP/new.html');
    }

    public function createAction(){
        // we create a new user and passing data from POST to the USER model constructor
        $user = new User($_POST);

        // after User model constructor created an instance and assigned values to it's properties, save the user
        // check whether a user has been saved successfully
        if($user->save()){
            // render the success page
            View::renderTemplate("SignUP/success.html");
        } else {
            // render template with user object passed in as a parameter (with errors array)
            View::renderTemplate("SignUP/new.html", ['user' => $user]);
        }


    }


}