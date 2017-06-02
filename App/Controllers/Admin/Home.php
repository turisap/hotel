<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 19-May-17
 * Time: 10:15 AM
 */

namespace App\Controllers\Admin;


use App\Authentifiacation;
use App\Models\Admin\Booking;
use Core\View;

class Home  extends \Core\Admin {

    // action filter which gets current user for all action methods and requires admin access
    public function before()
    {
        parent::requireAdmin();
        $this->user = Authentifiacation::getCurrentUser(); // get current user
        Booking::automaticBookingsDeletion();              // automatically delete all old bookings

    }


    // renders home admin panel
    public function indexAction(){

        View::renderTemplate('Admin/Home/index.html');

    }



}