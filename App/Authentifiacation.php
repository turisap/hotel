<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 2:28 PM
 */

namespace App;

use \App\Models\User;


// THIS CLASS ONLY FOR AUTHENTIFACATION METHODS
class Authentifiacation extends \Core\Controller {


    // this method requieres authorisation to access a page and remembers originally requested page if a user . THIS IS AN ACTION FILTER
    public function before(){
        // remeber requested page before redirect to login
        self::rememberRequestedPage();
        // if user isn't logged in, redirect to login
        if( ! self::isLoggedIn()){
            self::redirect('/login/new');
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


    public static function login(){
        // echo $_REQUEST['email'] . '  ' . $_REQUEST['password'];
        $user = User::authenticate($_POST['password'], $_POST['email']);
        if($user) {
            // regenerate session for safety purposes
            session_regenerate_id(true);
            //set some session vars and redirect
            $_SESSION['user_id'] = $user->id;
            $_SESSION['first_name'] = $user->first_name;
            return true;
        }
        return false;
    }

    // check whether user is logged in
    public static function isLoggedIn(){
        return isset($_SESSION['user_id']);
    }

    // save requested page in a session var (used only if user requests a restricted page and redirects to a login page)
    public static function rememberRequestedPage(){
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    // get requested page from the previous method or if there is no one, just home page
    public static function getRequestedPage(){
        return $_SESSION['return_to'] ?? '/';
    }


}