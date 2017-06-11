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
    public function allNotificationsAction(){

        // query string from Only unread button
        $sort = $_GET['sort'] ?? false;

        // get all notifications or only unread if specified
        $notifications = $sort ? Notification::getAllNotifications(false, true, false) : Notification::getAllNotifications();

        if($notifications){

            View::renderTemplate('admin/notifications/all_notifications.html', [
                'notifications' => $notifications,
                'sort'          => $sort
            ]);

        } else {

            Flash::addMessage('There are no unread notifications', Flash::INFO);
            View::renderTemplate('admin/notifications/all_notifications.html', ['empty' => 1]);

        }

    }


    // this method sets a notification as viewed
    public static function setAsReadAction(){

        $notification_id = $_POST['notification_id'] ?? false;

        if($notification_id){

            Notification::setAsRead($notification_id);
            return true;

        }
        return false;
    }

    // this method deletes checked notifications
    public function deleteCheckedAction(){

        $ids = $_POST;

        if( ! empty($ids)){

            foreach ($ids as $id){
                $notification = Notification::delete($id);
            }

            self::redirect('/admin/notifications/all-notifications');

        }

    }

}