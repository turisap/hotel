<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 17-May-17
 * Time: 4:28 PM
 */

namespace App\Controllers;


use App\Authentifiacation;
use Core\View;

class Profile extends \Core\Controller {


    // this is an action filter which requires login before accessing pages from this controller
    public function before()
    {
        $this->requireLogin();                         // require to be logged in

    }

    public function showAction(){

        $user = Authentifiacation::getCurrentUser();
        View::renderTemplate('Profile/show.html', ['user' => $user]);
    }

    public function editAction(){

        $user = Authentifiacation::getCurrentUser();
        View::renderTemplate('profile/edit.html', ['user' => $user]);
    }
}