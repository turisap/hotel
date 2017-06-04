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

            if(Menu::save($params)){

                Flash::addMessage('Category has been saved');
                self::redirect('admin/restaurant/categories');

            } else {

                View::renderTemplate('/admin/restaurant/create_category.html', ['params' => $params]);

            }

        } else {

            Flash::addMessage('Please enter some data', Flash::INFO);
            View::renderTemplate('/admin/restaurant/create_category.html');

        }

    }



    // renders edit category page
    public static function editCategoryAction(){

    }








}