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

    // this action processes link from reset mail and outputs form for new password input
    public function passwordResetAction(){

        $token = $this->route_parametrs['token']; // first get the token from url
        $user = static::getUserOrExit($token); // get user model
        View::renderTemplate('Password/reset.html', ['token' => $token]); // pass token to the form in order to use it via a hidden input

    }

    // this method resets password
    public function resetPassword(){

        $token = $_POST['token'];                       // first get the token from the form from hidden input which was passed there in previous method
        $user = static::getUserOrExit($token);          // find user by token form url

        if($user->isSamePassword($_POST['password'])){   // call resetting password method in the user model

            if($user->resetPassword($_POST['password'])){

                View::renderTemplate('password/reset_success.html');

            } else {
                View::renderTemplate('password/reset.html', ['user' => $user, 'token' => $token]);
            }

        } else {                                        // redisplay form on failure and pass token with user model
            Flash::addMessage('You use your old password, please enter a new one', Flash::DANGER);
            View::renderTemplate('password/reset.html', ['user' => $user, 'token' => $token]);
        }
    }

    // this method get a user model or exit script
    protected function getUserOrExit($token){
        $user = User::findByPasswordResetToken($token);
        if($user){
            return $user;
        } else {
            Flash::addMessage('Your link is invalid', Flash::DANGER);
            self::redirect('/password/new');
            exit;
        }
    }










}