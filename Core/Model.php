<?php

namespace Core;

use PDO;
use App\Config;
/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class Model
{

    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {

            $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' .
                Config::DB_NAME . ';charset=utf8';
            $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

            // Throw an exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        return $db;
    }

    // finds everything by ID (photo, user, booking)
    public static function findById($id){

        $sql = 'SELECT * FROM ' . static::$db_table . ' WHERE id = :id';
        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    // this method deletes via id
    public function delete($id){

        $sql = 'DELETE FROM ' . static::$db_table . ' WHERE user_id = :id';

        $db = static::getDB();
        $statament = $db->prepare($sql);

        $statament->bindValue(':id', $id, PDO::PARAM_STR);
        $statament->execute();
    }



}
