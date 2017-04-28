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
            echo "$file Not fount";
        }
    }

    // method for rendering twig
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            //$loader = new \Twig_Loader_Filesystem('../App/Views');
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
        }

        echo $twig->render($template, $args);
    }

}