<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Dispatcher
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Dispatcher.php';

/**
 * Диспетчер, обрабатывающий обычный запрос типа internal
 * (file_get_contents('internal://...'))
 *
 * @category App
 * @package App_Server
 * @subpackage Dispatcher
 */
class App_Server_Dispatcher_Internal extends App_Server_Dispatcher
{

    /**
     * Конструктор
     *
     * @param App_Server_Request $request            
     * @return void
     */
    public function __construct($request)
    {
        $this->_request = $request;
    }

    /**
     * Инициализировать объект ответа
     *
     * @return void
     */
    protected function _initResponse()
    {
        require_once (APP_FRAMEWORK_MAIN_DIR . "Server/Response/Internal.php");
        $this->_response = new App_Server_Response_Internal();
    }

    /**
     * Запуск процесса диспетчеризации
     * (внешне этот метод схож с аналогичным методом для http-запроса,
     * но отличается тем, что данные отдаются без заголовков)
     *
     * @return void
     */
    public function run()
    {
        $this->_initResponse();
        $this->_initRouter();
        $this->_initView();
        
        try {
            $controller = $this->_router->route();
            $controller->run();
            
            if (! $this->_response->isRedirect()) {
                $this->_response->setBody($this->_view->out());
            }
        } catch (App_Server_Router_Exception $e) {
            // 404 error - page not found
            trigger_error($e->getMessage(), E_USER_NOTICE);
        } catch (Exception $e) {
            // 500 internal server error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}