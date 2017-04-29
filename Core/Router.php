<?php

namespace Core;

class Router {

    protected $routes = array();// this property is an assosiative array with routes
    protected $params = array();// this property is an array for saving a matched rout

    // this method adds a route to the routing table
    //$route is a query string, while $params are controllers and actions
    public function add($route, $params = []){
        //$this->routes[$route] = $parametr;
        // escaping slashes
        $route = preg_replace('/\//', '\\/', $route);
        // converting variable parts into named capture groups
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);
        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        //add start and end delimiters
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;


    }

    // this method gets all routes from the routes table (array)
    public function getRoutes(){
        return $this->routes;
    }

    // this method compares url query string with all routing table and finds a match
    public function match($url){

        // Match to the fixed url controller/action
        // $reg_exp = "/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/";
        foreach($this->routes as $route => $params){

            if(preg_match($route, $url, $matches)){
                //$params = [];
                foreach($matches as $key => $match){
                    if(is_string($key)){
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }

        }

        return false;
    }

    // this method returns array with parameters for routing
    public function getParams(){
        return $this->params;

    }

    public function dispatch($url){

        $url = $this->removeQueryString($url);
        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            //$controller = "App\Controllers\\$controller";
            $controller = $this->getNameSpace() . $controller;

            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controller_object, $action])) {
                    $controller_object->$action();

                } else {
                    //echo "Method $action (in controller $controller) not found";
                    throw new \Exception("Method $action in controller $controller not found");
                }
            } else {
                echo "Controller class $controller not found";
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            //echo 'No route matched.';
            throw new \Exception("No route matched", 404);
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    protected function removeQueryString($url){
        if($url != ''){
            $parts = explode('&', $url, 2);

            if(strpos($parts[0], '=') == false){
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }

    protected function getNameSpace(){
        $namespace = 'App\Controllers\\';
        if(array_key_exists('namespace', $this->params)){
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }

}
?>