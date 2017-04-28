<?php

namespace App\Controllers;

/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 9:44 AM
 */
class Home extends \Core\Controller {

    public function indexAction(){
        echo "Hello from Home controller index() method";
    }

    protected function before(){
        echo "Before function   ";
        //return false;
    }

    protected function after(){
        echo "  After function";
    }


}

?>