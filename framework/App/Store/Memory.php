<?php
require_once 'App/Store/Interface.php';

class App_Store_Memory implements App_Store_Interface
{

    protected $_model;

    protected $_defaultLifetime = 500;

    protected static $_clear = false;

    protected static $instance;

    private function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        
        if (array_key_exists('lifetime', $config['store']['memory']) && $config['store']['memory']['lifetime'] != null) {
            $this->_defaultLifetime = intval($config['store']['memory']['lifetime']);
        }
        
        $name = $config['store']['memory']['tableName'];
        
        $handlerClass = $config['store']['memory']['handler'];
        
        $res = App_Loader::load($handlerClass);
        if ($res) {
            $this->_model = new $handlerClass($name);
        } else {
            throw new Exception('You should set memory store handler');
        }
        
        $this->init();
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

    public function init()
    {
        $this->_model->clear();
    }

    public function get($key, $getArray = false)
    {
        return $this->_model->get($key, $getArray);
    }

    public function set($key, $value, $expire = null)
    {
        if (! $expire) {
            $expire = time('now') + $this->_defaultLifetime;
        } else {
            $expire = time('now') + $expire;
        }
        
        return $this->_model->set($key, $value, $expire);
    }

    public function isValidKey($key)
    {
        return $this->_model->isValidKey($key);
    }
}