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
    public $errors = array(); // an array for collecting errors messages

    public function __construct($data) {
        // here we make object's properties and their values out of POST array
        foreach($data as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }

    public function save(){

        // validate data first
        $this->validate();

        // if error[] array id empty, so no errors make a record in the database
        if(empty($this->errors)){
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (username, email, password_hash)
                     VALUES (:name, :email, :password_hash)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':name', $this->name, PDO::PARAM_STR);
            $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
            $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $statement->execute(); // because execute() returns true of false
        }
        return false; //if validation failed
    }

    // this method validates form data
    public function validate(){

        if($this->name == ''){
            $this->errors[] = "Please fill the name field";
        }

        /*if(static::emailExists($this->email)){
            $this->errors[] = "This email is already taken";
        }*/

        if(filter_var($this->email, FILTER_VALIDATE_EMAIL) === false){
            $this->errors[] = "Please enter a valid email";
        }
        if($this->password != $this->password_confirmation){
            $this->errors[] = "Passwords should be the same";
        }
        if(strlen($this->password) < 6){
            $this->errors[] = "Password should be at least 6 characters long";
        }
        if(preg_match('/.*[a-z]+.*/i', $this->password) == 0){
            $this->errors[] = "Password should have at least one letter";
        }
        if(preg_match('/.*\d+./i', $this->password) == 0){
            $this->errors[] = "Password should have at least one number";
        }


    }

   //check whether an email is already in the database
    public static function emailExists($email){

        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetch() !== false;
    }
}