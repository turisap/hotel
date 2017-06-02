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
use App\Models\Admin\Photo;
use App\Models\Admin\Search;
use \App\Token;
use App\Models\RememberedLogin;
use App\Models\Admin\Room;


class Test
{




    public function test(){
       print_r(Photo::findAllPhotosToONeRoom(37, true));
    }







}