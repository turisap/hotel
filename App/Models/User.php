<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 30-Apr-17
 * Time: 12:33 PM
 */

namespace App\Models;
use PDO;


class User extends \Core\Model {

    public function __construct($data) {
        // here we make object's properties and their values out of POST array
        foreach($data as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }

    public function save(){
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (username, email, password_hash) VALUES (:name, :email, :password_hash)';
        $db = static::getDB();
        $statement = $db->prepare($sql);

        $statement->bindValue(':name', $this->name, PDO::PARAM_STR);
        $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
        $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

        $statement->execute();
    }

}