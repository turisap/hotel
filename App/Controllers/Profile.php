<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 17-May-17
 * Time: 4:28 PM
 */

namespace App\Controllers;


use App\Authentifiacation;
use App\Flash;
use App\Models\User;
use Core\View;

class Profile extends \Core\Controller {


    // this is an action filter which requires login before accessing pages from this controller
    public function before()
    {
        $this->requireLogin();                         // require to be logged in

    }

    // renders show page
    public function showAction(){

        $user = Authentifiacation::getCurrentUser();
        View::renderTemplate('Profile/show.html', ['user' => $user]);
    }

    // renders edit page
    public function editAction(){

        $user = Authentifiacation::getCurrentUser();
        View::renderTemplate('profile/edit.html', ['user' => $user]);
    }

    // this method updates user's profile using POST data
    public function updateProfile(){

        $user = Authentifiacation::getCurrentUser();

        if($user->updateUser($_POST)){

            Flash::addMessage('Your profile has been updated successfully', Flash::SUCCESS);
            self::redirect('/profile/show');

        } else {

            Flash::addMessage('Unsuccessful update');
            View::renderTemplate('profile/edit.html', ['user'=> $user]); // user object with all errors in errors array

        }
    }


}