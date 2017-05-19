<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 10:53 AM
 */

namespace Core;

use \App\Flash;
use App\Authentifiacation;

abstract class Controller {

    protected $route_parametrs = [];
    public function __construct($route_parametrs) {
        $this->route_parametrs = $route_parametrs;
    }

    public function __call($name, $arguments)
    {
        $method = $name . "Action";
        if(method_exists($this, $method)){
            if($this->before() !== false){
                call_user_func_array([$this, $method], $arguments);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method in controller " . get_class($this));
        }
    }

    protected function before(){

    }

    protected function after(){
        //echo "After function";
    }

    public static function redirect($url){
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }

    // this method can be used for requiring authorisation before accesssing a page (or extend Authentification class with action filter)
    public function requireLogin(){
        if( ! Authentifiacation::isLoggedIn()){
            Authentifiacation::rememberRequestedPage();
            Flash::addMessage('You need to login to view this page', Flash::INFO);
            self::redirect('/login/new');
        }
    }

    // this method requires to be admin to access all admin pages and redirects the user if not
    public function requireAdmin(){
       if( ! Authentifiacation::isAdmin()){
           Flash::addMessage('You need to have admin status to access this page', Flash::INFO);
           self::redirect('/home/index-action');
       }
    }

}
