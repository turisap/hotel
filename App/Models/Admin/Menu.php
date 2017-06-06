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

    // finds an item by id from both tables
    public static function getById($id, $table){

        $sql = 'SELECT * FROM ' . static::$db_tables[$table] . ' WHERE ';
        $sql .= ($table == 0) ? ' category_id = :id' : ' course_id = :id';

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetch();

    }


    // this method saves categories and courses
    public function saveCategory($table_index){

        // validate data first
        $this->validateCategory();

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
    protected function validateCategory(){


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

        if($this->categoryExists($this->category_name, $this->category_id ?? null)){ //category_id only on edit existing category to prevent error appearing if we wan tot keep the same name
            $this->errors[] = 'Category name is already taken';
        }


    }


    // checks if a category with the same name already exists
    public static function categoryExists($category_name, $id_ignore = []){

        $category = static::findByName(trim($category_name), 0);

        if($category){

            if($category->category_id != $id_ignore){
                return true;
            }

        }

        return false;
    }

    // finds an item by name (from both tables)
    public static function findByName($name, $table){

        $sql = 'SELECT * FROM ' . static::$db_tables[$table] . ' WHERE ';
        $sql .= ($table == 0) ? ' category_name = :name' : ' course_name  = :name';

        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':name', $name, PDO::PARAM_STR);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetch();

    }


    // updates category and courses
    public function updateCategory(){

        $this->validateCategory();

        if(empty($this->errors)){

            $sql = 'UPDATE ' . static::$db_tables[0] . ' SET category_name = :category_name, category_description = :category_description WHERE category_id = :category_id';

            $db  = static::getDB();
            $stm = $db->prepare($sql);

            $stm->bindValue(':category_name', $this->category_name, PDO::PARAM_STR);
            $stm->bindValue(':category_description', $this->category_description, PDO::PARAM_STR);
            $stm->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);

            return $stm->execute();

        }

        return false; // on validation failure
    }


    // this method deletes both category or a course
    public function deleteItem($table){

        $sql = 'DELETE FROM ' . static::$db_tables[$table] . ' WHERE ';

        // choose which table delete from
        $sql .= ($table == 0) ? 'category_id = :id' : 'course_id = :id';

        $db  = static::getDB();
        $stm = $db->prepare($sql);

        // choose which id property to use
        $id = ($table == 0) ? $this->category_id : $this->course_id;

        $stm->bindValue(':id', $id, PDO::PARAM_INT);

        return $stm->execute();


    }


    // use findAll() mehtod in Core\Model to obtain all courses using array with database tables
    public static function getAllCoursesWithCategoryNames(){

        $sql = 'SELECT courses.*, meal_categories.category_name FROM courses LEFT JOIN meal_categories USING (category_id)';

        $db  = static::getDB();
        $stm = $db->prepare($sql);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetchAll();

    }


    // creates record in the database
    public function saveCourse(){

        $this->validateCourse();

        if(empty($this->errors)){

            $sql = 'INSERT INTO ' . static::$db_tables[1] . ' (category_id, course_name, description, price)
             VALUES (:category_id, :course_name, :description, :price)';

            $db  = static::getDB();

            $stm = $db->prepare($sql);
            $stm->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
            $stm->bindValue(':course_name', $this->course_name, PDO::PARAM_STR);
            $stm->bindValue(':description', $this->course_description, PDO::PARAM_STR);
            $stm->bindValue(':price', $this->course_price, PDO::PARAM_INT);

            return $stm->execute() ? $db->lastInsertId() : false;    // return the last inserted id on success and false on failure

        }

        return false; // on validation failure

    }


    // this method validates users inputs
    protected function validateCourse(){


        if($this->course_name == ''){
            $this->errors[] = 'Course name cannot be empty';
        }

        if($this->course_description == ''){
            $this->errors[] = 'Course description shouldn\'t be empty';
        }

        if(strlen($this->course_name) < 2){
            $this->errors[] = 'Course name should be at least 2 characters long';
        }

        if(strlen($this->course_description) < 10){
            $this->errors[] = 'Course description should be at least 10 characters long';
        }

        if( ! is_numeric($this->course_price) && ! empty($this->course_price)){
            $this->errors[] = 'Course price should be a number';
        }

        if($this->courseExists($this->course_name, $this->course_id ?? null)){ //category_id only on edit existing category to prevent error appearing if we wan tot keep the same name
            $this->errors[] = 'Course name is already taken';
        }


    }

    // checks if a category with the same name already exists
    public static function courseExists($course_name, $id_ignore = []){

        $course = static::findByName(trim($course_name), 1);

        if($course){

            if($course->course_id != $id_ignore){
                return true;
            }

        }

        return false;
    }


    // this method deletes a group of items based on thier IDs
    public static function deleteItems($ids, $table){

        if(is_array($ids)){

            foreach ($ids as $id){

                // get Menu object based on id
                $item = self::getById($id, $table);

                // get Photo object for only courses
                $table == 0 ?: $photo = Photo::findPhotoByCourseId($id);

                // delete photo to the course if it was course (categories don't have photos)
                $table == 0 ?: Photo::delete($photo->id);

                // delete item
                $item->deleteItem($table);

            }

            return true;
        }

        return false;

    }





}