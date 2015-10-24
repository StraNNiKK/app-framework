<?php
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Store/Interface.php';

class App_Store_Session implements App_Store_Interface
{

    protected static $_firstConstruct = true;

    protected static $_instance = array();

    protected $_store = null;

    protected $_types = null;

    protected static $_mainKey = 'namespaces';

    protected static $_storeNamespaces = 'store';

    protected static $_typeNamespaces = 'types';

    protected static $_defaultNamespace = 'default';

    private function __construct($namespace)
    {
        if (! array_key_exists(self::$_mainKey, $_SESSION)) {
            $_SESSION[self::$_mainKey] = array();
        }
        
        if (! is_array($_SESSION[self::$_mainKey])) {
            $_SESSION[self::$_mainKey][self::$_storeNamespaces] = array();
            $_SESSION[self::$_mainKey][self::$_typeNamespaces] = array();
        }
        
        if (! $this->_store) {
            $this->_store = & $_SESSION[self::$_mainKey][self::$_storeNamespaces][$namespace];
            $this->_types = & $_SESSION[self::$_mainKey][self::$_typeNamespaces][$namespace];
            
            if (! is_array($this->_store)) {
                $this->clearNamespace();
            }
        }
    }

    private function __clone()
    {}

    public static function setDefaultNamespace($namespace)
    {
        self::$_defaultNamespace = $namespace;
    }

    public static function getInstance($namespace = null)
    {
        if (self::$_firstConstruct) {
            self::$_firstConstruct = false;
            
            $config = App_Server::getConfig();
            
            if (array_key_exists('session', $config['store']) && array_key_exists('defaultNamespace', $config['store']['session']) && strval($config['store']['session']['defaultNamespace']) != '') {
                self::$_defaultNamespace = $config['store']['session']['defaultNamespace'];
            }
        }
        
        if (! App_Session::getInstance()->checkStarted()) {
            App_Session::getInstance()->init();
            App_Session::getInstance()->start();
        }
        
        $namespace = ($namespace != null) ? $namespace : self::$_defaultNamespace;
        
        if (! isset(self::$_instance[$namespace])) {
            self::$_instance[$namespace] = new self($namespace);
        }
        
        return self::$_instance[$namespace];
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->_store)) {
            
            $val = $this->_store[$key];
            
            if (array_key_exists($key, $this->_types)) {
                $type = $this->_types[$key];
                if (in_array($type, array(
                    'object',
                    'array'
                ))) {
                    return unserialize($val);
                }
            }
            
            return $val;
        }
        
        return null;
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function set($key, $value)
    {
        $type = $this->__getType($value);
        
        if ($type) {
            $this->_types[$key] = $type;
            $this->_store[$key] = $this->__packValue($value);
        } else {
            $this->_store[$key] = $value;
        }
    }

    public function remove($key)
    {
        if (array_key_exists($key, $this->_store)) {
            
            unset($this->_store[$key]);
            
            if (array_key_exists($key, $this->_types)) {
                unset($this->_types[$key]);
            }
        }
    }

    public function clearNamespace()
    {
        $this->_store = array();
        $this->_types = array();
    }

    public function isValidKey($key)
    {
        if (array_key_exists($key, $this->_store)) {
            return true;
        } else {
            return false;
        }
    }

    private function __getType($val)
    {
        if (is_object($val)) {
            return 'object';
        } elseif (is_array($val)) {
            return 'array';
        } else {
            return null;
        }
    }

    private function __packValue($val)
    {
        return serialize($val);
    }

    private function __unpackValue($str)
    {
        return unserialize($str);
    }
}