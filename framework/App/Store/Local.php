<?php
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Store/Interface.php';


class App_Store_Local implements App_Store_Interface
{

    protected static $_firstConstruct = true;

    protected static $_instance;

    protected $_data = array();

    protected static $_defaultNamespace = 'default';

    protected static $_currentNamespace = null;

    private function __construct()
    {
        if (self::$_firstConstruct) {
            self::$_firstConstruct = false;
            
            $config = App_Server::getConfig();
            
            if (array_key_exists('local', $config['store']) && array_key_exists('defaultNamespace', $config['store']['local']) && strval($config['store']['local']['defaultNamespace']) != '') {
                self::$_defaultNamespace = $config['store']['local']['defaultNamespace'];
            }
        }
        
        self::$_currentNamespace = self::$_defaultNamespace;
    }

    private function __clone()
    {}

    public static function getInstance($namespace = null)
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        
        self::$_currentNamespace = (strval($namespace) != '') ? $namespace : self::$_defaultNamespace;
        
        return self::$_instance;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function get($key)
    {
        if (array_key_exists(self::$_currentNamespace, $this->_data) && (array_key_exists($key, $this->_data[self::$_currentNamespace]))) {
            return $this->_data[self::$_currentNamespace][$key];
        }
        
        return null;
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function set($key, $value)
    {
        if (! array_key_exists(self::$_currentNamespace, $this->_data)) {
            $this->_data[self::$_currentNamespace] = array();
        }
        
        $this->_data[self::$_currentNamespace][$key] = $value;
    }

    public function isValidKey($key)
    {
        if (! array_key_exists(self::$_currentNamespace, $this->_data) || ! array_key_exists($key, $this->_data[self::$_currentNamespace])) {
            return false;
        } else {
            return true;
        }
    }
}