<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 04-Jun-17
 * Time: 10:01 AM
 */

namespace App\Models\Admin;


use PDO;


class Menu extends \Core\Model {

    public static $db_tables = ['meal_categories', 'courses']; // two tables which are used in this model
    public $errors = array();                              // array for keeping validation errors


    // supplied with $_POST
    public function __construct($item=[]) {

        // here we creates properties and their values out of keys and values
        foreach($item as $key => $value){
            $this->$key = $value;
            //echo $key;
        }

    }


    // gets all categories with descriptions from the database
    public static function getAllCategories(){

       return static::findAll(static::$db_tables[0]);

    }


    // this method saves categories and courses
    public function saveCategory($table_index){

        // validate data first
        $this->validate();

        // if array with errors is empty there are no errors and we can save a category
        if(empty($this->errors)){

            $sql = 'INSERT INTO ' . static::$db_tables[$table_index] . '(category_name, category_description) VALUES(:category_name, :category_description)';

            $db  = static::getDB();
            $stm = $db->prepare($sql);
            $stm->bindValue(':category_name', $this->category_name, PDO::PARAM_STR);
            $stm->bindValue(':category_description', $this->category_description, PDO::PARAM_STR);

            return $stm->execute();

        }

       return false;
    }


    // this method validates users inputs
    protected function validate(){


        if($this->category_name == ''){
            $this->errors[] = 'Category name cannot be empty';
        }

        if($this->category_description == ''){
            $this->errors[] = 'Category description shouldn\'t be empty';
        }

        if(strlen($this->category_name) < 3){
            $this->errors[] = 'Category name should be at least 3 characters long';
        }

        if(strlen($this->category_description) < 10){
            $this->errors[] = 'Category description should be at least 10 characters long';
        }


    }

}