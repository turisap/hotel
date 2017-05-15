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
           //set some session vars and redirect
           $_SESSION['user_id'] = $user->id;
           $_SESSION['first_name'] = $user->first_name;
           static::redirect('/');
       } else {
           View::renderTemplate('Login/new.html',['email' => $_POST['email']]);
       }

    }

    public static function logOut(){
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
        static::redirect('/');
    }


}