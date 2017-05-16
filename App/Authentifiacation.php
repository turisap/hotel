<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 2:28 PM
 */

namespace App;

use \App\Models\User;
use \App\Models\RememberedLogin;


// THIS CLASS ONLY FOR AUTHENTIFACATION METHODS
class Authentifiacation extends \Core\Controller {

    public function before(){

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

        // forget remembered login
        self::forgetLogin();
    }


    // logs user in and assigns some session vars
    public static function login($user, $remember_me){
        // echo $_REQUEST['email'] . '  ' . $_REQUEST['password'];

        //if remember me checkbox was checked, make a record in thei database
        if($remember_me){
            if($user->rememberLogin()){
                setcookie('remember_me', $user->remember_token, $user->expire_timestamp, '/');
            }
        }

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
        if(isset($_SESSION['user_id'])){
           return true;
        } elseif(isset($_COOKIE['remember_me'])) {
            return true;
        } else {
            return false;
        }
    }

    // save requested page in a session var (used only if user requests a restricted page and redirects to a login page)
    public static function rememberRequestedPage(){
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    // get requested page from the previous method or if there is no one, just home page
    public static function getRequestedPage(){
        return $_SESSION['return_to'] ?? '/';
    }

    // returns currently logged in user from session or cookie
    public static function getCurrentUser(){

       if(isset($_SESSION['user_id'])){
           return User::findByID($_SESSION['user_id']); // find user using session
       } else {
           return static::loginFromCookie(); // find user using cookie
       }

    }

    // logins user from a cookie
    public static function loginFromCookie(){

        $cookie = $_COOKIE['remember_me'] ?? false; // first chech whether the cookie was set

        if($cookie){
             $remembered_login = RememberedLogin::findByToken($cookie); // get a record from remembered_login table as an object

            if($remembered_login && $remembered_login->hasExpired()){ // if a record was found
                $user = $remembered_login->getUser(); // get user object
                static::login($user, false);
                return $user;
            }
        }
    }

    protected static function forgetLogin(){

        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie){

            $remember_login = RememberedLogin::findByToken($cookie);
            if($remember_login){
                $remember_login->deleteLogin();
            }

            setcookie('remember_me', '', time() - 3600);
        }
    }


}