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

    // get request from selectboxes in search room page and renders template with subcategories in the second selectbox
    public function searchCategoriesAction(){

        // get data from post (category)
        $category = $_POST['categories'] ?? false;

        //print_r($category);


        if($category){

            // get subcategories from search model
            $subcategories = Search::findSearchSubcategories($category);
            Flash::addMessage('Please check subcategory');
            View::renderTemplate('Admin/bookings/find_room.html', ['subcategories' => $subcategories, 'category' => $category]);

        }
    }

    // process search form on submission (apply button)
    public static function searchRoomsAction(){

        // first get data from the POST array
        $data = $_POST ?? false;

        print_r(Search::assemblySearchsentence($data));

        /*// if there is data from form
        if($data){

            $custom_search_results = Search::findCustomSearch($data);
            // if search was successful create a sentence about user's search (like 'Your search was rooms with city view and aircon)
            if($custom_search_results){
                $search_sentence = Search::assemblySearchSentence($data);
            }

        }*/
    }



}