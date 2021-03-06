<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 8:05 PM
 */

namespace Core;


class View
{

    public static function render($view, $params = []){

        // make vars and their values out of $params array
        extract($params, EXTR_SKIP);
        $file = "../App/Views/$view"; // relative to Core derictory

        if(is_readable($file)){
            require $file;
        } else {
            throw new \Exception("$file Not fount");
        }
    }

    // method for rendering twig (actually it was the next method, we made it to get template for email)
    public static function renderTemplate($template, $args = [])
    {
      echo static::getTemplate($template, $args);
    }

    // method for rendering twig
    public static function getTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            //$loader = new \Twig_Loader_Filesystem('../App/Views');
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
            $twig->addGlobal('session', $_SESSION);// add session array to twig global to use it across all templates
            $twig->addGlobal('is_logged_in', \App\Authentifiacation::isLoggedIn()); // add logged in status for global twig usage
            $twig->addGlobal('current_user', \App\Authentifiacation::getCurrentUser());// add current user for global twig usage
            $twig->addGlobal('flash_messages', \App\Flash::getFlashMessage()); // add flash messages for twig global usage
            $twig->addGlobal('site_name', \App\Config::SITE_NAME); // add site name for twig global usage
            $twig->addGlobal('notifications_global', \App\Models\Admin\Notification::getGlobalPackage()); // getting notifications to show in the navbar
            $twig->addGlobal('currency_sign', \App\Config::CURRENCY);
        }

        return $twig->render($template, $args);
    }

}