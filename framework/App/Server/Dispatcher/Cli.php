<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Dispatcher
 * @version    $Id:$
 */
require_once 'App/Server/Dispatcher.php';
require_once 'App/Server/Dispatcher/Internal.php';

/**
 * Диспетчер, обрабатывающий обращение из командной строки через (php-cli)
 *
 * @category App
 * @package App_Server
 * @subpackage Dispatcher
 */
class App_Server_Dispatcher_Cli extends App_Server_Dispatcher_Internal
{

    /**
     * Конструктор
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Инициализация объекта запроса (~ App_Server_Request)
     *
     * @return void
     */
    protected function _initRequest()
    {
        require_once 'App/Server/Request/Internal.php';
        
        $path = 'http://internal' . $GLOBALS['argv'][1];
        
        $this->_request = new App_Server_Request_Internal($path);
    }

    /**
     * Процесс диспетчеризации
     *
     * @return void
     */
    public function run()
    {
        $this->_initRequest();
        parent::run();
    }
}