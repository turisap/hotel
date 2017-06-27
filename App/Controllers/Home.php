<?php

namespace App\Controllers;
use App\Config;
use App\Flash;
use App\Mail;
use \Core\View;

/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 9:44 AM
 */
class Home extends \Core\Controller {

    public function indexAction(){
        // here we pass the name of a file to render as well as some data in array to display in view
        //View::render("Home/index.php", ['name' => 'David', 'colors' => ['blue', 'red', 'black']]);
        View::renderTemplate("home/index.html");
    }

    // takes message from the footer form
    public function contactUs(){
        $data = $_POST ?? false;
        $address = $data['email'] ?? false;
        $subject = $data['name'] ?? false;
        $subject .=' sent you a message';
        $message = $data['message'] ?? false;
        $message .= '. Email to answer is ' . $address;

        if($address && $subject && $message){
            Mail::send('kirill-shakirov@mail.ru', $address, $subject, $message);
            Mail:: send($data['email'], 'kirill-shakirov@mail.ru', 'Confirmation', 'We received your message and will respond you soon');
            Flash::addMessage('Your message has been sent');
            self::redirect('/');
        } else {
            Flash::addMessage('Your message has not been sent, try again', Flash::DANGER);
            self::redirect('/');
        }
    }

    protected function before(){

    }

    protected function after(){

    }


}

?>