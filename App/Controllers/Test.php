<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 4:29 PM
 */

namespace App\Controllers;

use \App\Authentifiacation;
use App\Calendar;
use App\Mail;
use App\Models\Admin\Booking;
use App\Models\Admin\Notification;
use App\Models\Admin\Photo;
use App\Models\Admin\Search;
use App\Models\Review;
use \App\Token;
use App\Models\RememberedLogin;
use App\Models\Admin\Room;
use App\Models\Admin\Menu;


class Test
{




    public function test(){

        //print_r(Notification::getAllNotifications());
        //print_r(Notification::getNotificationsInfo(4, 1));
        //print_r(Notification::getGlobalPackage());
        //Notification::sinceLastVisit();
        //print_r($_SESSION['new_notifications']);
        //print_r(Notification::showUnviewedNotifications());


        //print_r(Notification::getNotificationsInfo(1, 14));

        print_r(Room::findAll(null, 3, 5));
    }









}