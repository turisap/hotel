<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 10:53 AM
 */

namespace Core;


abstract class Controller {

    protected $route_parametrs = [];
    public function __construct($route_parametrs) {
        $this->route_parametrs = $route_parametrs;
    }

    public function __call($name, $arguments)
    {
        $method = $name . "Action";
        if(method_exists($this, $method)){
            if($this->before() !== false){
                call_user_func_array([$this, $method], $arguments);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method in controller " . get_class($this));
        }
    }

    protected function before(){
        //echo "Before function";
    }

    protected function after(){
        //echo "After function";
    }
}