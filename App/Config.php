<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 5.4
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'hotel';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database Password
     * @var string
     */
    const DB_PASSWORD = '';

    /*errors dispalying only for users with authorised access to it */
    const SHOW_ERRORS = true;

    // secret key for token hashing
    const SECRET_KEW_HASHING = 'r7OOn90q3swhv64AbLE80N9ufEsHL63W';

    // website name
    const SITE_NAME = 'MyhotelSystem';
}
