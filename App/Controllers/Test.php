<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15-May-17
 * Time: 4:29 PM
 */

namespace App\Controllers;

use \App\Authentifiacation;
use \App\Token;
use App\Models\RememberedLogin;


class Test
{


    public static function test(){
      print_r(Authentifiacation::getCurrentUser());
    }


}