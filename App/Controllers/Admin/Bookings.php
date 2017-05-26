<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 24-May-17
 * Time: 9:44 AM
 */

namespace App\Controllers\Admin;


use App\Flash;
use App\Models\Admin\Photo;
use App\Models\Admin\Search;
use Core\View;
use App\Models\Admin\Room;

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

        // if there is data from form
        if($data){

            $results = Search::findCustomSearch($data);
            // if search was successful create a sentence about user's search (like 'Your search was rooms with city view and aircon)
            if($results){

                $results_with_photos = array(); // array for found rooms with photos

                // for each room found append data about main photo to display in search results
                foreach ($results as $key => $value){
                    $photo = Photo::findAllPhotosToONeRoom($value->id, true);
                    $results_with_photos[] = array_merge((array)$value, $photo);
                }

               $search_sentence = Search::assemblySearchSentence($data);
                View::renderTemplate('admin/bookings/find_room.html', [
                    'rooms' => $results_with_photos,
                    'sentence' => $search_sentence
                ]);

            }

        } else {
            Flash::addMessage('There was a problem processing your request, please try again');
            View::renderTemplate('admin/bookings/find_room.html');
        }
    }

    /*
    public static function checkAction(){
        print_r(Room::findById(18));
    }*/



}