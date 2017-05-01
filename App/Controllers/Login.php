<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-May-17
 * Time: 3:23 PM
 */

namespace App\Controllers;
use \Core\View;


class Login extends \Core\Controller {

    // this method just shows the login page
    public function newAction(){
        View::renderTemplate('Login/new.html');
    }

}