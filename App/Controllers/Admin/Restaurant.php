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
use App\Models\Admin\Photo;
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

        $courses = Menu::getAllCoursesWithCategoryNamesAndPhotos();
        //print_r($courses);
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

        $data = $_POST;
        $file = $_FILES['photo'] ?? false;

        // get list of all categories to pass them to the view in order to get them in selectbox in the case of errors
        $categories = Menu::getAllCategories();


        if(!empty($data) && $file){

            // create a new Photo object using POST data and a new Menu objects
            $photo = new Photo($file);
            $course = new Menu($data);

            if($photo && $course){

                $course_id = $course->saveCourse(); //save() method returns false on failure and last inserted id on success

                if($course_id){  // if id is present (the course was saved)


                    // save photo
                    if($photo->save(false, false, $course_id)){

                        // if a picture was saved as well, redirect to all courses
                        Flash::addMessage('Course was successfully created');
                        self::redirect('/admin/restaurant/all-courses');

                    } else {

                        // delete course if a picture wasn't saved
                        Flash::addMessage('There was a problem saving this photo, try again');

                        // get an object of course
                        $course = Menu::getById($course_id, 1);

                        // delete the course on photo saving failure
                        $course->deleteItem(1); // 1 is a tabel index in $db_tables array for Menu model

                        // path photo object with errors to the view
                        View::renderTemplate('admin/restaurant/create_course.html', [
                            'course'     => $course,
                            'photo'      => $photo,
                            'categories' => $categories

                        ]);

                    }

                }  else { // course wasn't saved, no course id

                    Flash::addMessage('There was a problem saving the course, please try again');
                    View::renderTemplate('admin/restaurant/create_course.html', [
                        'course' => $course,
                        'categories' => $categories
                    ]);

                }


            } else {

                Flash::addMessage('there was a problem processing your request, try again');
                self::redirect('/admin/restaurant/all-courses');

            }


        } else {

            Flash::addMessage('there was a problem processing your request, try again');
            self::redirect('/admin/restaurant/all-courses');


        }

    }



    // validates that there is no such an item name in the database (both category and  course names)
    public function validateCourseName(){

        $is_valid = ! Menu::courseExists($_GET['course_name'], $_GET['ignore_id'] ?? null); // check the email in the database,
        // get request from profile/edit
        header('Content-type: application/json'); //
        echo json_encode($is_valid); //echo out true or false for ajax

    }


    // this method deletes checked courses on all courses page
    public function deleteChecked(){

        // array with checked checkboxes
        $data = $_POST;


        if(!empty($data)){

            if(Menu::deleteItems($data, 1)){

                count($data) == 1 ? Flash::addMessage('Course was deleted successfully') : Flash::addMessage('Courses were deleted successfully');
                self::redirect('/admin/restaurant/all-courses');

            } else {

                Flash::addMessage('There was a problem deleting these courses, pleasy try again');
                self::redirect('/admin/restaurant/all-courses');

            }

        } else {

            Flash::addMessage('Nothing to delete');
            self::redirect('/admin/restaurant/all-courses');

        }

    }


    // renders edit course page
    public function editCourse(){

        $course_id = $_GET['id'] ?? false;

        // get a course with picture
        if($course_id){

            // find that course (this method finds a particular course if supplied with id)
            $course = Menu::getAllCoursesWithCategoryNamesAndPhotos($course_id);

            if($course){

                // get list of all categories to pass them to the view in order to get them in selectbox
                $categories = Menu::getAllCategories();

                View::renderTemplate('/admin/restaurant/edit_course.html', [
                    'course'     => $course,
                    'categories' => $categories
                ]);
              

            }



        }



    }





















}