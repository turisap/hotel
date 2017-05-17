<?php
session_start();

/***************** Routing *************/


/* Twig */
// we changed Twig's autoloader to composer's one  because it includes all autoloaders from installed packages through composer
require_once dirname(__DIR__) . '/vendor/autoload.php';



/* Autoload */
/* WE DON'T NEED TO INCLUDE ALL OUR OWN CLASSES BECAUSE WE HAVE ALREADY INCLUDED THEM IN
spl_autoload_register(function($class){
    $root = dirname(__DIR__); // get the parent directory
    $file = $root . '/' .  str_replace('\\', '/', $class) . '.php';
    if(is_readable($file)){
        require $root . '/' .  str_replace('\\', '/', $class) . '.php';
    }
});*/

/* Error and exception handling */
error_reporting(E_ALL); // set up report about all types of errors (instead of making it in the php.ini for all scr
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');



$router = new Core\Router();

// add routes to the routing table
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('posts', ['controller' => 'Posts', 'action' => 'index']);
$router->add('posts/new', ['controller' => 'Posts', 'action' => 'new']);
$router->add('{controller}/{action}');
$router->add('admin/{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}');
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);
$router->add('{controller}/{action}'); // this one for signup page (localhost/signup/index)
$router->add('login', ['controller' => 'Login', 'action' => 'new']); // this route for the login page
$router->add('{controller}/{action}/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']); // this route for taking token from email link (password reset)
$router->add('{controller}/{action}/{token:[\da-f]+}', ['controller' => 'SignUP', 'action' => 'activate']); // this route for taking token from email link (account activation)

/*
// display the routing table
    echo "<pre>";
    //var_dump($router->getParams());
    var_dump(htmlspecialchars(print_r($router->getRoutes(), true)));
    echo "</pre>";

// Match the requested rout
$url = $_SERVER['QUERY_STRING'];
if($router->match($url)){
    echo '<pre>';
    var_dump($router->getParams());
    echo '<pre>';
} else {
    echo "No route found for URL '$url'";
}*/

$router->dispatch($_SERVER['QUERY_STRING']);


?>