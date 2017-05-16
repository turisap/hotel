<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 16-May-17
 * Time: 3:29 PM
 */

namespace App\Controllers;


use App\Flash;
use App\Models\User;
use Core\View;

class Password extends \Core\Controller {

    // renders page with reset form
    public function newAction(){
        View::renderTemplate('Password/new.html');
    }

    // this method resets user's password
    public function resetRequest(){

        $user = User::findByEmail($_POST['email']); // find a user via email

        if($user){ // if user found reset password
            $user->sendPasswordResetLink($_POST['email']);
            View::renderTemplate('Password/success.html');
        } else {
            Flash::addMessage('Sorry, but there is no user with this email', Flash::INFO);
            View::renderTemplate('password/new.html', ['email' => $_POST['email']]);
        }
    }

    // this action processes link from reset mail
    public function passwordResetAction(){

        $token = $this->route_parametrs['token']; // first get the token from url
        $user = User::findByPasswordResetToken($token);
        if($user){
            View::renderTemplate('Password/reset.html');
        } else {
            Flash::addMessage('Your link is invalid', Flash::DANGER);
            self::redirect('/password/new');
        }

    }










}