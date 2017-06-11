<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 11-Jun-17
 * Time: 3:08 PM
 */

namespace App;


class Pagination {


    // constructor with some defuault values which are assinged to the object's properties
    function __construct($page = 1, $items_per_page = 4, $items_total_count = 0){
        $this->current_page = $page;
        $this->items_per_page = $items_per_page;
        $this->items_total = $items_total_count;

        $methods = get_class_methods($this);

        foreach ($methods as $method){
            if(method_exists($this, $method) && $method != '__construct'){
                $this->$method = call_user_func([$this, $method]);
            }
        }

    }


    // this method for swithing to the next page
    public function next(){
        return $this->current_page + 1;
    }

    // this method for swithing to the previous page
    public function previous(){
        return $this->current_page - 1;
    }

    // this method calculates the total amount of pages
    public function page_total(){
        // round up division
        return ceil($this->items_total/$this->items_per_page);
    }

    // this method checks whether there is a next page
    public function has_next(){
        return ($this->next() <= $this->page_total()) ? true : false;
    }

    // this method checks whether there is a previous page
    public function has_prev(){
        return ($this->previous() >= 1) ? true : false;
    }

    // this method gives the offset from the beginning of pagination
    public function offset(){
        return ($this->current_page - 1) * $this->items_per_page;
    }



}