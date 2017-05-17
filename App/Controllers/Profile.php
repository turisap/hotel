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


    // this is an action filter for all action methods in this controller
    public function before()
    {
        // parent::before(); // this line for inhering and not overriding parent's before method

        // here we assing a whole user object to Login class user property
        $this->user = Authentifiacation::getCurrentUser();

        // require authorisation to access
        $this->requireLogin();
    }

    // renders show page
    public function showAction(){

        //$user = Authentifiacation::getCurrentUser();                       // this line went to the before() action filter
        View::renderTemplate('Profile/show.html', ['user' => $this->user]);
    }

    // renders edit page
    public function editAction(){

        //$user = Authentifiacation::getCurrentUser();                       // this line went to the before() action filter
        View::renderTemplate('profile/edit.html', ['user' => $this->user]);
    }

    // this method updates user's profile using POST data
    public function updateProfileAction(){

        // $user = Authentifiacation::getCurrentUser();                       // this line went to the before() action filter

        if($this->user->updateUser($_POST)){

            Flash::addMessage('Your profile has been updated successfully', Flash::SUCCESS);
            self::redirect('/profile/show');

        } else {

            Flash::addMessage('Unsuccessful update');
            View::renderTemplate('profile/edit.html', ['user'=> $this->user]); // user object with all errors in errors array

        }
    }


}