<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 30-Apr-17
 * Time: 12:33 PM
 */

namespace App\Models;
use App\Config;
use App\Flash;
use App\Mail;
use App\Token;
use Core\View;
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

        if(static::emailExists($this->email, $this->id ?? null)){ // pass this-id in order to check whether it an existing user (see emailExists() method)
            $this->errors[] = "This email is already taken";               // and make the user id optional, only if isset, because no id on sign up
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
        // if there is one, check Password and return user object
        if($user){
           if(password_verify($password, $user->password_hash)){
               return $user;
           }
        }
        return false;
    }

   //check whether an email is already in the database and checks whether this email belongs to an existing user
    public static function emailExists($email, $id_ignore = []){

        $user = self::findByEmail($email); // find a user based on email

        if($user){

            if($user->id != $id_ignore){ // check whether user's id isn't equal to ignore id (not existing user resetting password)
                return true;
            }
        }
        return false;
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


    // sends a password reset link
    public function sendPasswordResetLink($email){

        $user = static::findByEmail($email);

        if($user){
            $user->writePasswordResetTokenHash(); // write record with token and expiry to the database
            $user->passwordResetLink(); // and send email

        }
    }

    // this method prepares a link and sends email
    protected function passwordResetLink(){

        // find user model to pass it to the template for email
        $user = static::findByEmail($this->email);
        if($user){
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/password-reset/' . $this->password_reset_token;
            $body = View::getTemplate('Password/reset_email.html', [
                'user' => $user,
                'site_name' => Config::SITE_NAME,
                'url'       => $url
                ]);
            Mail::send($this->email, 'Password reset', 'Reseting password', $body);
        }

    }

    // creates a record in the database with reset token hash and its expiry
    protected function writePasswordResetTokenHash(){

        $token = new Token();  // generete a new token
        $this->password_reset_token = $token->getValue(); // assign value of a token to the object's property
        $token_hash = $token->getTokenHash();

        $expire_stamp = time() + 60 * 60 * 2;

        $sql = 'UPDATE ' . static::$db_table . ' SET password_reset_hash = :token_hash, password_reset_expiry = :expire_stamp
         WHERE id = :id';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
        $statement->bindValue(':expire_stamp', date('Y-m-d H-i-s',$expire_stamp), PDO::PARAM_STR);
        $statement->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $statement->execute();

    }

    // this method finds a user and returns its model based on a token from link from resetting email (as well check expiry of that token)
    public static function findByPasswordResetToken($token)
    {

        $token = new Token($token); // create a token object based on existing token value
        $token_hash = $token->getTokenHash(); // and get its hash

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE password_reset_hash = :token_hash';
        $db = static::getDB();

        $statement = $db->prepare($sql);
        $statement->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        $user = $statement->fetch(); // only this fetch() function returns object, not execute()

        // here we check whether token expired or not
        if ($user) {
            if (strtotime($user->password_reset_expiry) > time()) {
                return $user; // return user model only if token hasn't expired
            } else {
                Flash::addMessage('Sorry, but your link has expired', Flash::DANGER);
            }
        }
    }

    // this method resets password in the database
    public function resetPassword($new_password){

        $this->password = $new_password;        // assign password from the form to the object's property
        $this->validate();                      // validate password

        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

        if(empty($this->errors)){               // begin process of password resetin only in array with errors is empty (no errors)

            $sql = 'UPDATE ' . static::$db_table . ' SET 
                       password_hash = :password_hash,
                       password_reset_hash = NULL,
                       password_reset_expiry = NULL
                       WHERE id = :id';

            $db = static::getDB();
            $stm = $db->prepare($sql);
            $stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stm->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stm->execute();
        }
        return false;                            // return false on update failure
    }





















}