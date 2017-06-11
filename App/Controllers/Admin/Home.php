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
use App\Models\Admin\Notification;
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

        // check new unviewed notifications first (set sessions with their IDs)
        Notification::sinceLastVisit();

        // get unviewed notifications
        $notifications = Notification::showUnviewedNotifications();
        //print_r($notifications);

        if($notifications){ // get their types count if there are new notifications


            $events = Notification::getTypesCount($notifications);

            // delete old notifications from the database
            Notification::deleteOldNotifications();

            View::renderTemplate('Admin/Home/index.html', [
                'events'        => $events,
                'notifications' => $notifications
            ]);
        } else {

            // render just empty template
            View::renderTemplate('Admin/Home/index.html');

        }

    }



}