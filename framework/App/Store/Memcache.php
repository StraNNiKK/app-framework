<?php
require_once 'App/Store/Interface.php';

class App_Store_Memcache implements App_Store_Interface
{

    protected static $instance;

    public static $store;

    private function __construct()
    {
        self::$store = new Memcache();
        
        try {
            self::$store->connect('localhost', 11211);
            self::$store->setCompressThreshold(20000, 0.2);
        } catch (Exception $e) {
            throw new Exception('Cant connect to memcache server');
        }
    }

    private function __clone()
    {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        return self::$store->get($key);
    }

    public function set($key, $val, $expire = 60)
    {
        if (strlen(self::$store->get($key))) {
            self::$store->replace($key, $val, false, $expire);
        } else {
            self::$store->set($key, $val, false, $expire);
        }
    }

    public function isValidKey($key)
    {
        if (strlen(self::$store->get($key))) {
            return true;
        } else {
            return false;
        }
    }
}