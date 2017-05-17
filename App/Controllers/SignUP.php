<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/29/2017
 * Time: 8:47 PM
 */

namespace App\Controllers;
use App\Flash;
use \Core\View;
use App\Models\User;


class SignUP extends \Core\Controller
{

    public function newAction(){
        View::renderTemplate('SignUP/new.html');
    }

    public function createAction(){
        // we create a new user and passing data from POST to the USER model constructor
        $user = new User($_POST);

        // after User model constructor created an instance and assigned values to it's properties, save the user
        // check whether a user has been saved successfully
        if($user->save()){
            //these lines redirect to another method (other address in the browser) in order to prevent form resubmittion
            // this is redirect which is made in terms of MVC
            $user->sendActivationEmail();                                              // send an activation email
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/signup/success');
        } else {
            // render template with user object passed in as a parameter (with errors array)
            View::renderTemplate("SignUP/new.html", ['user' => $user]);
        }


    }

    public function successAction(){
        // render the success page
        View::renderTemplate("SignUP/success.html");
    }

    // this method activates user's account via email link
    public function accountActivationAction(){

        $token = $this->route_parametrs['token'];            // get token value from activation account url

        $user = User::findUserByToken($token);               // get a user model based on that token

        if($user){

            if($user->activationLinkHasExpired()){          // check whether activation link has expired

                User::accountActivation($token);                  // activated account
                self::redirect('/SignUP/activated');

            } else {
                Flash::addMessage('Your activation link has expired, please signup again', Flash::DANGER);
                self::redirect('/signup/new');
            }

        }

    }

    public function activatedAction(){
        View::renderTemplate('SignUP/success.html');
    }




}