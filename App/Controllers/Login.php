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


class Login extends \Core\Controller {

    // this method just shows the login pagenew.html
    public function newAction(){
        View::renderTemplate('Login/new.html');
    }


    // this method logs user in and redirects on success
    public function createAction(){
       // echo $_REQUEST['email'] . '  ' . $_REQUEST['password'];
       $user = User::authenticate($_POST['password'], $_POST['email']);
       if($user){
           echo '<h1>Success</h1>';
       } else {
           View::renderTemplate('Login/new.html');
       }

    }


}