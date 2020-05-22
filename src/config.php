<?php
namespace devmazon\myorm;

use PDO;
use PDOException;

class Config{

    private static $db;
    private static $errorDb;
    
   
    
    public static function db(){
    
        try{
            $config = array();
        if (ENVIRONMENT == 'development') {     
            $config['driver'] = DRIVER;       
            $config['dbname'] = DB_NAME;
            $config['host'] = HOST_DEVELOPMENT;
            $config['dbuser'] = DB_USER_DEVELOPMENT;
            $config['dbpass'] = DB_PASS_DEVELOPMENT;
        } else {
            $config['driver'] = DRIVER;  
            $config['dbname'] = DB_NAME;
            $config['host'] = HOST_PRODUCTION;
            $config['dbuser'] = DB_USER_PRODUCTION;
            $config['dbpass'] = DB_PASS_PRODUCTION;
        }

        $config['default_lang'] = 'pt-br';

        self::$db = new PDO($config['driver'].":dbname=" . $config['dbname'] . ";host=" . $config['host'], $config['dbuser'], $config['dbpass']);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception){
            self::$errorDb = $exception;
        }

        return self::$db;
    
    }

    public static function db_another($data_base){

        try{
            $config = array();
        if (ENVIRONMENT == 'development') {    
        
            $config['driver'] = constant("DB_{$data_base}_DRIVER");       
            $config['dbname'] = constant("DB_{$data_base}_NAME");
            $config['host'] = constant("DB_{$data_base}_HOST_DEVELOPMENT");
            $config['dbuser'] = constant("DB_{$data_base}_USER_DEVELOPMENT");
            $config['dbpass'] = constant("DB_{$data_base}_PASS_DEVELOPMENT");
        } else {
            $config['driver'] = constant("DB_{$data_base}_DRIVER");       
            $config['dbname'] = constant("DB_{$data_base}_NAME");
            $config['host'] = constant("DB_{$data_base}_HOST_PRODUCTION");
            $config['dbuser'] = constant("DB_{$data_base}_USER_PRODUCTION");
            $config['dbpass'] = constant("DB_{$data_base}_PASS_PRODUCTION");
        }

        $config['default_lang'] = 'pt-br';

        self::$db = new PDO($config['driver'].":dbname=" . $config['dbname'] . ";host=" . $config['host'], $config['dbuser'], $config['dbpass']);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception){
            self::$errorDb = $exception;
        }

        return self::$db;
    }

    public static function getErrorDb(){
        return self::$errorDb;
    }
}
