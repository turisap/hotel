<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 04-Jun-17
 * Time: 10:01 AM
 */

namespace App\Models\Admin;


class Menu extends \Core\Model {



    // gets all categories with descriptions from the database
    public static function getAllCategories(){

       return static::findAll('meal_categories');

    }

}