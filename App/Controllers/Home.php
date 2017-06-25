<?php

namespace App\Controllers;
use \Core\View;

/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 9:44 AM
 */
class Home extends \Core\Controller {

    public function indexAction(){
        // here we pass the name of a file to render as well as some data in array to display in view
        //View::render("Home/index.php", ['name' => 'David', 'colors' => ['blue', 'red', 'black']]);
        View::renderTemplate("home/index.html");
    }

    protected function before(){

    }

    protected function after(){

    }


}

?>