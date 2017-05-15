<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01-May-17
 * Time: 3:23 PM
 */

namespace App\Controllers;


use \Core\View;
use \App\Models\User;
use App\Authentifiacation;
use App\Flash;


class Login extends \Core\Controller {

    // this method just shows the login pagenew.html
    public function newAction(){
        View::renderTemplate('Login/new.html');
    }


    // this method logs user in and redirects on success
    public function createAction(){
       if(Authentifiacation::login()){
           // redirect user to the page which he originally requested
           Flash::addMessage('Successfully logged in', Flash::SUCCESS);
           static::redirect(Authentifiacation::getRequestedPage());
       } else {
           Flash::addMessage('You are not logged in', Flash::DANGER);
           View::renderTemplate('Login/new.html',['email' => $_POST['email']]);
       }

    }

    // logging a user out
    public static function logOut(){
        Authentifiacation::logOut();
        // we need to redirect to another method because by this point session is destroyed and we cannot use flash messages
        self::redirect('/login/show-logout-message');
    }

    // add message on logging out
    public static function showLogoutMessage(){
        Flash::addMessage('You are successfully logged out', Flash::SUCCESS);
        self::redirect('/login/new');
    }


}