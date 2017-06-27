<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 27-Jun-17
 * Time: 8:21 PM
 */

namespace App\Controllers;


use Core\View;

class Amenities extends \Core\Controller {


    public function before()
    {

    }

    public function beachGallery(){
        View::renderTemplate('/amenities/beach.html');
    }

}