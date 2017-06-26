<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 5.4
 */
class Config
{

    // database credentials
    const DB_HOST = 'localhost';
    const DB_NAME = 'hotel';
    const DB_USER = 'root';
    const DB_PASSWORD = '';


    /*errors dispalying only for users with authorised access to it */
    const SHOW_ERRORS = true;

    // secret key for token hashing
    const SECRET_KEW_HASHING = 'r7OOn90q3swhv64AbLE80N9ufEsHL63W';

    // website name
    const SITE_NAME = 'Roksana Blue';

    // if this set to true then users can use the same password on reset
    const SAME_PASSWORD = false;

    // max file size to upload
    const MAX_FILE_SIZE = 10000000;

    // directory separotor
    const DS = DIRECTORY_SEPARATOR;

    // the number of days for keeping records of bookings and automatic deletion after passing, must be in '- n days' format
    const DAYS_BOOKING_KEEPING = '-180 days';

    // the number of days when booking becomes upcoming
    const UPCOMING_LIMIT = '10 days';

    // currency (sign of currency which will be displayed with prices)
    const CURRENCY = '$';

    // days keeping notifications
    const DAYS_KEEP_NOTIFICATIONS = '-10 days';

}
