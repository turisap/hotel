<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 16-May-17
 * Time: 3:20 PM
 */

namespace App;

use PHPMailer;


class Mail
{

    // mailing using PHPMailer
    public static function send($to, $from, $subject, $text){

        //require dirname(__DIR__) . '/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
        $mail = new PHPMailer;

       //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'kirillshakirov57@gmail.com';                 // SMTP username
        $mail->Password = 'turisap89';                           // SMTP Password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom($from);
        $mail->addAddress($to);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $text;


        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }

}