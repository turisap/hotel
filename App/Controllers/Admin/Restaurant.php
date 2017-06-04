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
                'categories' => $categories,
                'site_name'  => Config::SITE_NAME
        ]);


    }


    // renders create category page
    public function createCategory(){

        View::renderTemplate('/admin/restaurant/create_category.html');

    }


    // this method saves categories
    public function saveCategory(){

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

            View::renderTemplate('admin/restaurant/edit_category.html', ['category' => $category]);

        } else {
            self::redirect('/admin/restaurant/categories');
        }

    }


    // processes form on edit category and updates it in the database
    public function updateCategory(){

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






}