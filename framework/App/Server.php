<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Error/Handler.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Stack.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Router.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Controller.php';

/**
 * Объект данного класса используется для диспетчеризации запроса
 * в зависимости от его типа.
 *
 * Определяется тип запроса (обычный http, internal, cli и т.п.) и
 * создается объект диспетчер, который отвечает за весь весь жизненный цикл
 * обработки запроса.
 *
 * @category App
 * @package App_Server
 */
class App_Server
{

    /**
     * Объект диспетчера.
     *
     * @var App_Server_Dispatcher
     */
    protected $_dispatcher;

    /**
     * С виду кажется, что класс реализует синглтон,
     * хотя это не так.
     * Конструктор private лишь для того, что бы при создании
     * каждой отдельной сущности, она сразу помещалась в стек. Поэтому для создания
     * сущностей сервера используется метод App_Server::newInstance()
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * Получить последний объект из стека
     *
     * @static
     *
     * @return App_Server
     */
    public static function getInstance()
    {
        $instance = App_Server_Stack::last();
        if (! is_object($instance)) {
            $instance = new App_Server();
        }
        
        return $instance;
    }

    /**
     * Создать новый объект
     *
     * @static
     *
     * @return App_Server
     */
    public static function newInstance()
    {
        $new = new App_Server();
        App_Server_Stack::push($new);
        
        return $new;
    }

    /**
     * Получить массив настроек приложения.
     * Фактически алиас для App_Application::getConfig()
     *
     * @static
     *
     * @return array
     */
    public static function getConfig()
    {
        return App_Application::getConfig();
    }

    public static function getConfigObj()
    {
        return App_Application::getConfigObj();
    }

    /**
     * Получить объект диспетчера.
     *
     * @return App_Server_Dispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * Получить объект запроса.
     *
     * @static
     *
     * @return App_Server_Request
     */
    public static function getRequest()
    {
        $server = App_Server::getInstance();
        return $server->getDispatcher()->getRequest();
    }

    /**
     * Получить объект ответа.
     *
     * @static
     *
     * @return App_Server_Response
     */
    public static function getResponse()
    {
        $server = App_Server::getInstance();
        return $server->getDispatcher()->getResponse();
    }

    /**
     * Получить объект роутера.
     *
     * @static
     *
     * @return App_Server_Router
     */
    public static function getRouter()
    {
        $server = App_Server::getInstance();
        return $server->getDispatcher()->getRouter();
    }

    /**
     * Получить объект вида.
     *
     * @static
     *
     * @return App_Server_View
     */
    public static function getView()
    {
        $server = App_Server::getInstance();
        return $server->getDispatcher()->getView();
    }

    /**
     * Определяем тип запроса - в зависимости от этого создаем
     * соотвествующий диспетчер и передаем управление ему.
     *
     * @param App_Server_Request $request            
     * @return App_Server_Response
     */
    public function run($request = null)
    {
        $this->_errorHandler = new App_Server_Error_Handler();
        set_error_handler(array(
            &$this->_errorHandler,
            'handler'
        ));
        
        if ($request && $request instanceof App_Server_Request_Internal) {
            
            require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Dispatcher/Internal.php';
            $this->_dispatcher = new App_Server_Dispatcher_Internal($request);
            
            // для использования в командной строке с использованием php-cli,
            // например как таск крона
            // php -f index.php -- /path/to/execute/controller
        } else 
            if (array_key_exists('argv', $GLOBALS) && array_key_exists('argc', $GLOBALS) && intval($GLOBALS['argc']) > 0) {
                
                require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Dispatcher/Cli.php';
                $this->_dispatcher = new App_Server_Dispatcher_Cli();
            } else {
                
                require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Dispatcher/Http.php';
                $this->_dispatcher = new App_Server_Dispatcher_Http();
            }
        
        $this->_dispatcher->run();
        
        restore_error_handler();
        
        return $this->_dispatcher->getResponse();
    }

    /**
     * Создается новая сущность сервера,
     * ей передается управление в ожидании
     * объекта типа response, который в последствии преобразуется
     * в строку и отправляется как конечный ответ веб-приложения.
     * После этого работа приложения считается завершенной.
     *
     * @static
     *
     * @return void
     */
    public static function go()
    {
        $response = App_Server::newInstance()->run();
        echo $response->toString();
    }
}