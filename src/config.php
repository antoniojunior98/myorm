<?php
namespace DevMazon\myORM;
require 'environment.php';
use PDO;
use PDOException;


// global $config;
// global $db;

// $config = array();


// $config['default_lang'] = 'pt-br';


class Config{

    private $db;
    private $errorDb;

    public function db(){
        try{

        if (ENVIRONMENT == 'development') {
            define("BASE_URL", "http://localhost/Servidor/adm_devmazon/");
            $config['dbname'] = DB_NAME;
            $config['host'] = HOST_DEVELOPMENT;
            $config['dbuser'] = DB_USER_DEVELOPMENT;
            $config['dbpass'] = DB_PASS_DEVELOPMENT;
        } else {
            define("BASE_URL", "http://localhost/Servidor/adm_devmazon/");
            $config['dbname'] = DB_NAME;
            $config['host'] = HOST_PRODUCTION;
            $config['dbuser'] = DB_USER_PRODUCTION;
            $config['dbpass'] = DB_PASS_PRODUCTION;
        }
        $this->db = new PDO("mysql:dbname=" . $config['dbname'] . ";host=" . $config['host'], $config['dbuser'], $config['dbpass']);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception){
            $this->errorDb = $exception;
        }

        return $this->db;
    }
}
