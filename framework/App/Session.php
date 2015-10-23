<?php
require_once 'App/Session/Handler/Interface.php';

class App_Session
{

    protected static $instance;

    protected $_config;

    private static $_sessionStarted = false;

    private static $_writeClosed = false;

    private static $_rememberMeInSeconds = 1440; // 20 минут

    protected static $_flashMessagesNamespace = '_flashMessages_';

    private function __construct()
    {}

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
        $config = App_Server::getInstance()->getConfig();
        $this->_config = $config['store']['session'];
        
        $this->initHandler();
        $this->initSessionLifetime();
    }

    public function initHandler()
    {
        if (array_key_exists('handler', $this->_config) && $this->_config['handler'] != null) {
            $handlerClass = $this->_config['handler'];
            $res = App_Loader::load($handlerClass);
            if ($res) {
                $saveHandler = new $handlerClass();
                if ($saveHandler instanceof App_Session_Handler_Interface) {
                    
                    session_set_save_handler(array(
                        &$saveHandler,
                        'open'
                    ), array(
                        &$saveHandler,
                        'close'
                    ), array(
                        &$saveHandler,
                        'read'
                    ), array(
                        &$saveHandler,
                        'write'
                    ), array(
                        &$saveHandler,
                        'destroy'
                    ), array(
                        &$saveHandler,
                        'gc'
                    ));
                }
            }
        }
    }

    public function initSessionLifetime()
    {
        if (array_key_exists('cookieLifeTime', $this->_config) && intval($this->_config['cookieLifeTime']) > 0) {
            self::$_rememberMeInSeconds = intval($this->_config['cookieLifeTime']);
        } else {
            self::$_rememberMeInSeconds = intval(ini_get('session.cookie_lifetime'));
        }
    }

    public static function start()
    {
        self::$_sessionStarted = session_start();
    }

    public static function regenerateId()
    {
        if (self::$_sessionStarted) {
            session_regenerate_id(true);
        }
    }

    public static function rememberMe($seconds = null)
    {
        $seconds = intval($seconds);
        $seconds = ($seconds > 0) ? $seconds : self::$_rememberMeInSeconds;
        
        $cookieParams = session_get_cookie_params();
        
        // set lifetime for cookie records on client side
        session_set_cookie_params($seconds, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure']);
        
        // Don't remember that this actions is only for client side
        // and sessions lifetime on server side is depends on session.gc_maxlifetime
        // in php.ini config. Default value is session.gc_maxlifetime = 1440 (20 minutes)
        
        self::regenerateId();
    }

    public static function checkStarted()
    {
        return self::$_sessionStarted;
    }

    public static function writeClose()
    {
        if (! self::$_sessionStarted || self::$_writeClosed) {
            return;
        }
        
        session_write_close();
        self::$_writeClosed = true;
    }

    public function __set($name, $value)
    {
        App_Session::getInstance()->set($name, $value);
    }

    public static function set($name, $value, $namespace = null)
    {
        App_Store_Session::getInstance($namespace)->set($name, $value);
    }

    public function __get($name)
    {
        return App_Session::getInstance()->get($name);
    }

    public static function get($name, $namespace = null)
    {
        return App_Store_Session::getInstance($namespace)->get($name);
    }

    public static function check($name, $namespace = null)
    {
        return App_Store_Session::getInstance($namespace)->isValidKey($name);
    }

    public static function remove($name, $namespace = null)
    {
        return App_Store_Session::getInstance($namespace)->remove($name);
    }

    public static function addFlashMessage($message)
    {
        $value = App_Store_Session::getInstance(self::$_flashMessagesNamespace)->get('message');
        
        if ($value == null) {
            $value = array();
        }
        array_push($value, $message);
        
        App_Store_Session::getInstance(self::$_flashMessagesNamespace)->set('message', $value);
    }

    public static function hasFlashMessages()
    {
        return App_Store_Session::getInstance(self::$_flashMessagesNamespace)->isValidKey('message');
    }

    public static function getFlashMessages()
    {
        $value = App_Store_Session::getInstance(self::$_flashMessagesNamespace)->get('message');
        App_Store_Session::getInstance(self::$_flashMessagesNamespace)->remove('message');
        
        return $value;
    }

    public static function clearFlashMessages()
    {
        App_Store_Session::getInstance(self::$_flashMessagesNamespace)->clearNamespace();
    }
}