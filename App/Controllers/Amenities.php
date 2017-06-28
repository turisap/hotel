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

    public function beachGalleryAction(){
        View::renderTemplate('/amenities/amenities.html', ['content' => 1]);
    }

    public function spaGalleryAction(){
        View::renderTemplate('/amenities/amenities.html', ['content' => 2]);
    }
    public function partyGalleryAction(){
        View::renderTemplate('/amenities/amenities.html', ['content' => 3]);
    }

}