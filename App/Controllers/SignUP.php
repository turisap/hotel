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
        $data = new User($_POST);

        // after User model constructor created an instance and assigned values to it's properties, save the user
        $data->save();

        // render the success page
        View::renderTemplate("SignUP/success.html");
    }


}