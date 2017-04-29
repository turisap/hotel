<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/29/2017
 * Time: 8:47 PM
 */

namespace App\Controllers;
use \Core\View;


class SignUP extends \Core\Controller
{

    public function newAction(){
        View::renderTemplate('SignUP/new.html');
    }

}