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


class Login extends \Core\Controller {

    // this method just shows the login pagenew.html
    public function newAction(){
        View::renderTemplate('Login/new.html');
    }


    // this method logs user in and redirects on success
    public function createAction(){
       if(Authentifiacation::login()){
           // redirect user to the page which he originally requested
           static::redirect(Authentifiacation::getRequestedPage());
       } else {
           View::renderTemplate('Login/new.html',['email' => $_POST['email']]);
       }

    }

    public static function logOut(){
        Authentifiacation::logOut();
    }


}