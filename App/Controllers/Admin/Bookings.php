<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 9:44 AM
 */

namespace App\Controllers\Admin;


use App\Flash;
use App\Models\Admin\Search;
use Core\View;

class Bookings extends \Core\Controller {

    // action filter requires to be admin to access all pages from this controller
    public function before()
    {
        //parent::before();
        $this->requireAdmin();
    }


    // this method renders form to create a booking
    public function createAction(){

        View::renderTemplate('admin/bookings/find_room.html');

    }

    // get request from selectboxes in search room
    public function searchCategoriesAction(){

        // get data from post (category)
        $category = $_POST['categories'] ?? false;

        print_r($category);


        if($category){

            // get subcategories from search model
            $subcategories = Search::findSearchSubcategories($category);
            Flash::addMessage('Please check subcategory');
            View::renderTemplate('Admin/bookings/find_room.html', ['subcategories' => $subcategories, 'category' => $category]);

        }
    }



}