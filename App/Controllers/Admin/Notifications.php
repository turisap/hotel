<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 07-Jun-17
 * Time: 7:58 PM
 */

namespace App\Controllers\Admin;

use \App\Models\Admin\Notification;
use \Core\View;
use \App\Flash;


class Notifications extends \Core\Admin {


    public function before()
    {
        parent::before(); // TODO: Change the autogenerated stub
    }


    // renders page with all notifications
    public function allUnreadNotifications(){

        $notifications = Notification::getAllUnreadNotifications();

        if($notifications){

            View::renderTemplate('admin/notifications/all_notifications.html', ['notifications' => $notifications]);

        } else {

            Flash::addMessage('There are no unread notifications',Flash::INFO);
            View::renderTemplate('admin/notifications/all_notifications.html', ['empty' => 1]);

        }

    }

}