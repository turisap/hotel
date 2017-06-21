<?php

namespace App\Controllers;
use \Core\View;
use App\Models\Post;

/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/28/2017
 * Time: 9:44 AM
 */
class Posts extends \Core\Controller {

    // before action filter which requires authorisation ONLY FOR THIS CONTROLLER
    public function before(){
        static::requireLogin();
    }


    public function indexAction(){
        //echo "Hello from Post controller index() method";
        //View::renderTemplate('Posts/edit.html');
        $posts = Post::getAll();
        View::renderTemplate('posts/edit.html', ['posts' => $posts]);
    }

    public function addNewAction(){
        echo "Hello from Post controller addNew() method";
        echo "<p>Test of query string: it should be here: <pre>" . htmlspecialchars(print_r($_GET, true)) ."</pre></p>";
    }

    public function edit(){
        echo "Hello from the Post class edit() method";
        echo "<p>Route parameters <pre>" . htmlspecialchars(print_r($this->route_parametrs, true)) . "</pre></p>";
    }

}

?>