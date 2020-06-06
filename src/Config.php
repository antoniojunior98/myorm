<?php
namespace devmazon\myorm;

use PDO;
use PDOException;

class Config{

    private static $db;
    private static $errorDb;
    
   
    
    public static function db(): ?PDO
    {
      
        if (empty(self::$db)) {
            try {
                self::$db = new PDO(
                    DB_CONFIG["driver"] . ":host=" . DB_CONFIG["host"] . ";dbname=" . DB_CONFIG["dbname"] . ";port=" . DB_CONFIG["port"],
                    DB_CONFIG["username"],
                    DB_CONFIG["password"],
                    DB_CONFIG["options"]
                );
            } catch (PDOException $exception) {
                self::$errorDb = $exception;
            }
        }

        return self::$db;
    }

    public static function db_another($data_base): ?PDO
    {

        if (empty(self::$db)) {
            try {
                self::$db = new PDO(
                    DB_CONFIG["driver"] . ":host=" . DB_CONFIG["host"] . ";dbname=" . DB_CONFIG["dbname"] . ";port=" . DB_CONFIG["port"],
                    DB_CONFIG["username"],
                    DB_CONFIG["password"],
                    DB_CONFIG["options"]
                );
            } catch (PDOException $exception) {
                self::$errorDb = $exception;
            }
        }

        return self::$db;
    }

    public static function getErrorDb(): ?PDOException
    {
        return self::$errorDb;
    }
}
