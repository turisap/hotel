<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 04-Jun-17
 * Time: 9:25 AM
 */

namespace App\Controllers\Admin;


use App\Config;
use \App\Models\Admin\Menu;
use Core\View;
use \App\Flash;


class Restaurant extends \Core\Admin {


    // action filter
    public function before(){
        parent::before();
    }


    // renders manage menu page
    public function categoriesAction(){

        $categories = Menu::getAllCategories();

        View::renderTemplate('admin/restaurant/all_categories.html', [
                'categories' => $categories
        ]);


    }


    // renders create category page
    public function createCategory(){

        View::renderTemplate('/admin/restaurant/create_category.html');

    }


    // this method saves categories
    public function saveCategoryAction(){

        $params = $_POST;

        if(!empty($params)){

            // create a new Menu object using POST data
            $category = new Menu($params);

            if($category->saveCategory(0)){

                Flash::addMessage('Category has been saved');
                self::redirect('/admin/restaurant/categories');

            } else {
                //print_r($category);
                Flash::addMessage('Please fix all errors');
                View::renderTemplate('/admin/restaurant/create_category.html', [
                    'category' => $category       // pass categor object to the view in order to access its POST data and errors array
                ]);

            }

        } else {

            Flash::addMessage('Please enter some data', Flash::INFO);
            View::renderTemplate('/admin/restaurant/create_category.html');

        }

    }



    // renders edit category page
    public function editCategoryAction(){

        $category_id = $_GET['id'] ?? false;

        if($category_id){

            $category = Menu::getById($category_id, 0);

            if($category){

                View::renderTemplate('admin/restaurant/edit_category.html', ['category' => $category]);

            } else {
                Flash::addMessage('This category doesn\'t exist');
                self::redirect('/admin/restaurant/categories');
            }

        } else {
            self::redirect('/admin/restaurant/categories');
        }

    }


    // processes form on edit category and updates it in the database
    public function updateCategoryAction(){

        $data = $_POST;
        $category_id = $_POST['category_id'] ?? false;

        if(!empty($data) && $category_id){

            $category = new Menu($data); // initialize a new object using POST data

            if($category){

                if($category->updateCategory()){

                    Flash::addMessage('The category was updated successfully', Flash::INFO);
                    self::redirect('/admin/restaurant/categories');

                } else {
                    Flash::addMessage('Please fix all errors', Flash::DANGER);
                    View::renderTemplate('/admin/restaurant/edit_category.html', ['category' => $category]); // category object with all errors
                }

            } else {

                Flash::addMessage('It looks like there is no such a category', Flash::INFO);
                self::redirect('/admin/restaurant/categories');

            }

        } else {

            Flash::addMessage('There was a problem processing your request, please try again', Flash::INFO);
            self::redirect('/admin/restaurant/categories');

        }

    }


    // processes request on category deletion
    public function deleteCategoryAction(){

        $category_id = $_GET['id'] ?? false;

        if($category_id){

            // second parametr is the index of table in $db_tables array
            $category = Menu::getById($category_id, 0);

            if($category->deleteItem(0)){

                Flash::addMessage('the category was succussfully deleted');
                self::redirect('/admin/restaurant/categories');

            } else {
                Flash::addMessage('There was a problem processing your request, try again');
                self::redirect('/admin/restaurant/edit-category?id=' . $category_id);
            }

        } else {
            Flash::addMessage('There no such a category');
            self::redirect('/admin/restaurant/categories');
        }

    }


    // renders template with all courses
    public function allCoursesAction(){

        $courses = Menu::getAllCourses();
        View::renderTemplate('admin/restaurant/all_courses.html', ['courses' => $courses]);

    }


    // renders create course page
    public function createCourseAction(){

        // get list of all categories to pass them to the view in order to get them in selectbox
        $categories = Menu::getAllCategories();

        View::renderTemplate('admin/restaurant/create_course.html', [
            'categories' => $categories
        ]);

    }


    // validates that there is no such an item name in the database (both category and  course names)
    public function validateCategoryName(){

        $is_valid = ! Menu::categoryExists($_GET['category_name'], $_GET['ignore_id'] ?? null); // check the email in the database,
        // get request from profile/edit
        header('Content-type: application/json'); //
        echo json_encode($is_valid); //echo out true or false for ajax

    }


    // create a course using data from the POST array
    public function saveCourse(){



    }



}