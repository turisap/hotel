<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 19-May-17
 * Time: 10:15 AM
 */

namespace App\Controllers\Admin;


use Core\View;

class Home  extends \Core\Controller {



    // renders home admin panel
    public function indexAction(){

        View::renderTemplate('Admin/Home/index.html');

    }






}