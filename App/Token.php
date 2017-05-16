<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 16-May-17
 * Time: 8:23 AM
 */

namespace App;

use \App\Config;


class Token {

    protected $token;

    //  this function creates a new random token or takes value of existing one
    public function __construct($existing_token = null){
        if($existing_token){
            $this->token = $existing_token;
        } else {
            $this->token = bin2hex(random_bytes(16));
        }
    }

    // returns token value
    public function getValue(){
        return $this->token;
    }

    // returns token hash
    public function getTokenHash(){
        return hash_hmac('sha256', $this->token, Config::SECRET_KEW_HASHING);
    }

}