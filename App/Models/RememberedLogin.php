<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 16-May-17
 * Time: 10:44 AM
 */

namespace App\Models;

use PDO;
use \App\Token;


class RememberedLogin extends \Core\Model {

    // finds a record in remembered_login table using token from cookie
    public static function findByToken($token_from_cookie){

        $token = new Token($token_from_cookie); // first create a token object using existing token value from the cookie
        $token_hash = $token->getTokenHash(); // and get its hash

        $sql = 'SELECT * FROM remembered_login WHERE token_hash = :token_hash';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // set fetch mode to this class

        $statement->execute();
        return $statement->fetch(); // returns an object of RememberedLogin class
    }

    public function getUser(){
        return User::findById($this->user_id);
    }
}