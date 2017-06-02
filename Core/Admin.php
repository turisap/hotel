<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 02-Jun-17
 * Time: 3:11 PM
 */

namespace Core;


class  Admin extends \Core\Controller {


    // this is an action filter for all controllers in Admin folder
    public function before() {

        $this->requireAdmin();

    }

}