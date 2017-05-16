<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 30-Apr-17
 * Time: 12:33 PM
 */

namespace App\Models;
use App\Token;
use PDO;


class User extends \Core\Model {

    public $errors = array(); // an array for collecting errors messages
    protected static $db_table = 'users';

    public function __construct($data=[]) {
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

            $sql = 'INSERT INTO users (first_name, last_name, email, password_hash)
                     VALUES (:first_name, :last_name, :email, :password_hash)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':first_name', $this->first_name, PDO::PARAM_STR);
            $statement->bindValue(':last_name', $this->last_name, PDO::PARAM_STR);
            $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
            $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $statement->execute(); // because execute() returns true of false
        }
        return false; //if validation failed
    }

    // this method validates form data
    public function validate(){

        if($this->first_name == ''){
            $this->errors[] = "Please fill the first name field";
        }

        if($this->last_name == ''){
            $this->errors[] = "Please fill the last name field";
        }

        if(static::emailExists($this->email)){
            $this->errors[] = "This email is already taken";
        }

        if(filter_var($this->email, FILTER_VALIDATE_EMAIL) === false){
            $this->errors[] = "Please enter a valid email";
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

    // authentifacates a user using POST array
    public static function authenticate($password, $email){
        // find a record in the database via email
        $user = static::findByEmail($email);
        // if there is one, check password and return user object
        if($user){
           if(password_verify($password, $user->password_hash)){
               return $user;
           }
        }
        return false;
    }

   //check whether an email is already in the database
    public static function emailExists($email){
        return static::findByEmail($email) !== false;
    }

    // finds a user by email
    public static function findByEmail($email){

        $sql = 'SELECT * FROM ' . self::$db_table . ' WHERE email = :email';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    // inserts a record into the remembered_login table with user's id, token hash and expire date
    public function rememberLogin(){
        // get a new token object
        $token = new Token();

        // sets value of a expire data and token value as an object properties
        $this->expire_timestamp = time() + 60 * 60 * 24 * 3; // set up expire three days after
        $this->remember_token = $token->getValue();

        $sql = 'INSERT INTO remembered_login (token_hash, user_id, expires_at) 
                VALUES (:token_hash, :user_id, :expires_at)';
        $db = static::getDB();
        $stm = $db->prepare($sql);

        $stm->bindValue(':token_hash', $token->getTokenHash(), PDO::PARAM_STR);
        $stm->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stm->bindValue(':expires_at', date('Y-m-d H-i-s', $this->expire_timestamp), PDO::PARAM_STR);

        return $stm->execute();
    }
}