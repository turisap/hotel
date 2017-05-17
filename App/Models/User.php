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
    protected static $column   = 'id';

    public function __construct($data=[]) {
        // here we make object's properties and their values out of POST array
        foreach($data as $key => $value){
            $this->$key = $value;
            //echo $key;
        }
    }

    public function save(){

        $this->activation_token = new Token();                                // generate a new activation token
        $token_hash = $this->activation_token->getTokenHash();                // and it's hash
        $this->activation_token_expiry = time() + 60 * 60 * 24;

        // validate data first
        $this->validate();

        // if error[] array id empty, so no errors make a record in the database
        if(empty($this->errors)){
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (first_name, last_name, email, password_hash, activation_hash, activation_hash_expiry)
                     VALUES (:first_name, :last_name, :email, :password_hash, :activation_hash, :activation_hash_expiry)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':first_name', $this->first_name, PDO::PARAM_STR);
            $statement->bindValue(':last_name', $this->last_name, PDO::PARAM_STR);
            $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
            $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $statement->bindValue(':activation_hash', $token_hash, PDO::PARAM_STR);
            $statement->bindValue(':activation_hash_expiry', date('Y-m-d H-i-s', $this->activation_token_expiry), PDO::PARAM_STR);

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

        if(isset($this->password)){                                              // validate password only if it was set

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


    }

    // authentifacates a user using POST array
    public static function authenticate($password, $email){
        // find a record in the database via email
        $user = static::findByEmail($email);
        // if there is one, check Password and return user object
        if($user && $user->isActivatedAccount()){                                  // checks if user activated its account
           if(password_verify($password, $user->password_hash)){
               return $user;
           }
        }
        return false;
    }

    // checks  if account was activated
    public function isActivatedAccount(){
        if($this->active){                                                         // if this column set to 0 its inactive
            return true;
        } else {
            Flash::addMessage('You should activate your account first', Flash::DANGER);
            return false;
        }
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
            $body = View::getTemplate('Password/activation_email.html', [
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

    // this method checks whether user uses the same password while resetting
    public function isSamePassword($new_password){

        if( ! Config::SAME_PASSWORD){                                                    // check if we set prohibition for using the same password on reset

            $sql = 'SELECT password_hash FROM ' . static::$db_table . ' WHERE id = :id'; // fid user's password
            $db  = static::getDB();

            $statement = $db->prepare($sql);
            $statement->bindValue(':id', $this->id, PDO::PARAM_INT);

            $statement->execute();

            $old_password_hash = $statement->fetchColumn();                              // to get just a single value, not array

            if(password_verify($new_password, $old_password_hash)){                      // check whether passwords are the same

                return false;

            } else {

                return true;

            }

        }

        return true;

    }

    // this method resets password in the database
    public function resetPassword($new_password){

        if(self::isSamePassword($new_password)){    // check if  passwords were the same and if it was allowed

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

        }
        return false;                                // return false on update failure
    }


    // this method prepares a link for account activation and sends email
    public function sendActivationEmail(){

            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/account-activation/' . $this->activation_token->getValue();
            $body = View::getTemplate('SignUP/activation_email.html', [
                'first_name' => $this->first_name,
                'site_name' => Config::SITE_NAME,
                'url'       => $url
            ]);
            Mail::send($this->email, 'Account activation', 'Account activation', $body);

    }

    // checks whether activation link expired
    public function activationLinkHasExpired(){
        if(strtotime($this->activation_hash_expiry) > time()){
            return true;
        }
        return false;
    }

    // this method activates account via token from email link
    public static function accountActivation($token){

        $token = new Token($token);                                   // first generate a new token object using existing token value from url
        $token_hash = $token->getTokenHash();

        $sql = 'UPDATE ' . static::$db_table . ' SET active = 1, activation_hash = NULL, activation_hash_expiry = NULL 
                WHERE activation_hash = :token_hash';

        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

        return $stm->execute();

    }

    // finds and returns a user model based on token from account activation url
    public static function findUserByToken($token){

        $token = new Token($token);                                   // first generate a new token object using existing token value from url
        $token_hash = $token->getTokenHash();

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE activation_hash = :token_hash';
        $db  = static::getDB();

        $stm = $db->prepare($sql);
        $stm->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
        $stm->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stm->execute();

        return $stm->fetch();

    }

    // this method deletes a user record from users table if there was an attempt to activate account via expired link
    public function deleteExpiredUser(){
        static::delete($this->id);
    }

    // this method updates user's profile on the edit page
    public function updateUser($data){


        if(empty($this->errors)) {  // update user only if there are no errors on submission

            // assing data from the POST array to object's properties
            $this->first_name   = $data['first_name'];
            $this->last_name    = $data['last_name'];
            $this->email        = $data['email'];
            isset($data['new_password']) ? $this->password = $data['new_password'] : null;  // because changing password is optional

            $sql = 'UPDATE ' . static::$db_table . ' SET first_name = :first_name, last_name = :last_name, email = :email, ';

              if(isset($this->password)){
                  $sql .= ' password_hash = :password_hash ';  // because changing password is optional
              }

            $sql .= ' WHERE id = :id';
            $db  = static::getDB();

            $stm = $db->prepare($sql);
            $stm->bindValue(':first_name', $this->first_name, PDO::PARAM_STR);
            $stm->bindValue(':last_name', $this->last_name, PDO::PARAM_STR);
            $stm->bindValue(':email', $this->email, PDO::PARAM_STR);

            if(isset($this->password)){
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT); // because changing password is optional
                $stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR); // because changing password is optional
            }

            $stm->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stm->execute();

        }

        return false;


    }
































}