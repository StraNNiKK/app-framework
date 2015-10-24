<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Application
 * @version    $Id:$
 */

// define start time constant
defined('START_TIME') || define('START_TIME', microtime(true));

// define framework main dir
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/');


require_once APP_FRAMEWORK_MAIN_DIR . 'Debug.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Functions.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Config.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Loader.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Event/Dispatcher.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Server.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Steam/Wrapper.php';

/**
 * Класс отвечает за первичную инициализацию приложения.
 *
 * Под первичной инициализацией понимается загрузка определеных данных
 * перед процессом диспетчеризации запроса.
 *
 * @category App
 * @package App_Application
 */
class App_Application
{

    /**
     * Массив настроек приложения.
     *
     * @static
     *
     * @var array
     */
    static $applicationConfig = null;

    /**
     * Массив настроек приложения в виде объекта.
     *
     * @static
     *
     * @var App_Config
     */
    static $applicationConfigObj = null;

    /**
     * Конструктор класса.
     *
     * Вызывает основные методы для инициализации приложения.
     *
     * @param string $configPath            
     * @return void
     */
    public function __construct($configPath = null)
    {
        new App_Functions();

        $this->_loadConfig($configPath);
        $this->_setDefaultTimezone();
        $this->_loadAutoloader();
        $this->_setErrorReporting();
        $this->_setDebugMode();
        $this->_setIncludePath();
        $this->_wrapperRegister();
        $this->_sessionInit();
        $this->_systemEventsInit();
    }

    /**
     * Инициализация автозагрузчика классов в зависимости от настроек приложения.
     *
     * @return void
     */
    protected function _loadAutoloader()
    {
        if (array_key_exists('autoload', self::$applicationConfig) && array_key_exists('enable', self::$applicationConfig['autoload']) && self::$applicationConfig['autoload']['enable'] == true) {
            spl_autoload_register(array(
                'App_Loader',
                'load'
            ));
        }
    }

    /**
     * Чтение настроек приложения из файла конфигурации.
     *
     * @param string $configPath            
     * @return void
     */
    protected function _loadConfig($configPath)
    {
        $configObj = App_Config::loadConfig($configPath, '.', true);
        self::$applicationConfig = $configObj->toArray();
        self::$applicationConfigObj = $configObj;
    }

    protected function _setDefaultTimezone()
    {
        if (array_key_exists('default_timezone', self::$applicationConfig) && strval(self::$applicationConfig['default_timezone']) != '') {
            date_default_timezone_set(self::$applicationConfig['default_timezone']);
        }
    }

    /**
     * Настройка сообщений об ошибках.
     *
     * @return void
     */
    protected function _setErrorReporting()
    {
        error_reporting(intval(self::$applicationConfig['errors']['error_reporting']));
        ini_set('display_errors', strval(self::$applicationConfig['errors']['display_errors']));
        ini_set('display_startup_errors', strval(self::$applicationConfig['errors']['display_startup_errors']));
        ini_set('log_errors', intval(self::$applicationConfig['errors']['log_errors']));
        ini_set('error_log', strval(self::$applicationConfig['errors']['error_log']));
    }

    /**
     * Отображать вспомогательные дампы.
     *
     * @return void
     */
    protected function _setDebugMode()
    {
        App_Debug::setAllow((bool) self::$applicationConfig['debug']['enable']);
        
        if (self::$applicationConfig['debug']['toolbar']['enable'] == true) {
            require_once APP_FRAMEWORK_MAIN_DIR . 'Debug/Toolbar.php';
            App_Debug::enableToolbar(true);
        }
    }

    /**
     * Настройка путей для файлов, подключаемых при помощи
     * include/require
     *
     * @return void
     */
    protected function _setIncludePath()
    {
        if (array_key_exists('includePathes', self::$applicationConfig) && is_array(self::$applicationConfig['includePathes'])) {
            $str = '';
            for ($i = 0; $i < count(self::$applicationConfig['includePathes']); $i ++) {
                $str .= self::$applicationConfig['includePathes'][$i] . PATH_SEPARATOR;
            }
            set_include_path($str . get_include_path());
        }
    }

    /**
     * Регистрация "внутреннего" протокола (internal://)
     *
     * Регистрируется новый псевдо-протокол, для имитации запросов внутри приложения.
     *
     * @return void
     */
    protected function _wrapperRegister()
    {
        stream_wrapper_register('internal', 'App_Server_Stream_Wrapper');
    }

    protected function _sessionInit()
    {
        if (array_key_exists('store', self::$applicationConfig) && array_key_exists('session', self::$applicationConfig['store'])) {
            if (array_key_exists('serverSessionLifetime', self::$applicationConfig['store']['session']) && intval(self::$applicationConfig['store']['session']['serverSessionLifetime']) > 0) {
                ini_set('session.gc_maxlifetime', intval(self::$applicationConfig['store']['session']['serverSessionLifetime']));
            }
            
            if (array_key_exists('autostart', self::$applicationConfig['store']['session']) && self::$applicationConfig['store']['session']['autostart'] == true) {
                require_once APP_FRAMEWORK_MAIN_DIR . 'Session.php';
                App_Session::getInstance()->init();
                App_Session::getInstance()->start();
            }
        }
    }

    protected function _systemEventsInit()
    {
        if (array_key_exists('config', self::$applicationConfig['events'])) {
            App_Event_Dispatcher::getInstance()->initSystemConfig(self::$applicationConfig['events']['config']);
        }
    }

    /**
     * Вернуть массив настроек.
     *
     * @static
     *
     * @return array массив настроек приложения
     */
    static public function getConfig()
    {
        return self::$applicationConfig;
    }

    static public function getConfigObj()
    {
        return self::$applicationConfigObj;
    }

    /**
     * Запуск процесса диспетчеризации запроса.
     *
     * @return void
     */
    public function run()
    {
        App_Server::go();
    }
}