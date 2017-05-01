<?php

//echo "Hello from the public ";

//echo 'Requested URL = "' . $_SERVER['QUERY_STRING'] . '"';

/***************** Routing *************/
//require("../Core/Router.php");
//require("../App/Controllers/Posts.php");
//$router = new Router();
//echo get_class($router);

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