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
use App\Pagination;
use \App\Token;
use App\Models\RememberedLogin;
use App\Models\Admin\Room;
use App\Models\Admin\Menu;


class Test
{




    public function test(){
        $paginate = new Pagination(1,5,15);
        print_r($paginate);
    }









}