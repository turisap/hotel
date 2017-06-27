<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 27-Jun-17
 * Time: 10:14 AM
 */

namespace App\Controllers;


use App\Models\Admin\Menu;
use App\Models\Admin\Search;
use Core\View;

class Restaurant extends \Core\Controller {

    public function before(){

    }


    // show dining page
    public function diningAction(){
        $categories = Menu::getAllCategories();
        View::renderTemplate('restaurant/dining.html', ['categories' => $categories]);
    }

    // show category
    public function showCategory(){
        $category = $_POST['category'] ?? false;

        if($category){
            $courses = Search::sortAllCourses($_POST);
            //print_r($courses);
            $categories = Menu::getAllCategories();
            View::renderTemplate('restaurant/show_courses.html', [
                'courses'    => $courses,
                'categories' => $categories,
                'category'   => $category
            ]);
        } else {
            self: self::redirect('/restaurant/dining');
        }
    }



}