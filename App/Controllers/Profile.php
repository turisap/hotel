<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 17-May-17
 * Time: 4:28 PM
 */

namespace App\Controllers;


use App\Authentifiacation;

class Profile extends \Core\Controller {


    public function before()
    {

    }

    public function showAction(){
        $this->requireLogin();

        print_r($_SESSION);

        echo '<hr/>';

        if(Authentifiacation::isLoggedIn()){
            echo "<h2>is logged in</h2>";
        }

        $user = Authentifiacation::getCurrentUser();
        if($user){
            echo '<h2>' . $user->first_name . '</h2>';
        }

        echo '<hr/>';

         print_r($_COOKIE);


    }
}