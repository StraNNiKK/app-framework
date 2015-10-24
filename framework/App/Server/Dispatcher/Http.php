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
 * Диспетчер, обрабатывающий обычный http-запрос
 *
 * @category App
 * @package App_Server
 * @subpackage Dispatcher
 */
class App_Server_Dispatcher_Http extends App_Server_Dispatcher
{

    /**
     * Инициализация объекта запроса (~ App_Server_Request)
     *
     * @return void
     */
    protected function _initRequest()
    {
        require_once (APP_FRAMEWORK_MAIN_DIR . "Server/Request/Http.php");
        $this->_request = new App_Server_Request_Http();
    }

    /**
     * Инициализация объекта ответа (~ App_Server_Response)
     *
     * @return void
     */
    protected function _initResponse()
    {
        require_once (APP_FRAMEWORK_MAIN_DIR . "Server/Response/Http.php");
        $this->_response = new App_Server_Response_Http();
    }

    /**
     * Инициализация основных объектов и запуск процесса диспетчеризации
     *
     * @return void
     */
    public function run()
    {
        $this->_initRequest();
        $this->_initResponse();
        
        try {
            $this->_initRouter();
            $this->_initView();
            
            App_Event_Observable_System::getInstance()->fire('beforeRoute');
            
            $controller = $this->_router->route();
            
            $this->_dispatch($controller);
        } catch (App_Server_Router_Exception $e) {
            // 404 error - page not found
            $this->_runError($e, '404');
        } catch (Exception $e) {
            // 500 internal server error
            trigger_error($e->getMessage(), E_USER_ERROR);
            $this->_runError($e, '500');
        }
    }

    /**
     * Запуст процесса диспетчеризации в случае вывода сообщения об ошибке
     *
     * @param Exception $errObj            
     * @param string $errNum            
     * @return void
     */
    protected function _runError($errObj, $errNum)
    {
        $this->_request->setDefaultExt();
        $this->_initDefaultView();
        
        $this->_response->setCode($errNum);
        
        try {
            $controller = $this->_router->route2errorPage($errObj, $errNum);
            $this->_dispatch($controller);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Процесс диспетчеризации: запуск соответствующего контроллера,
     * присоединение данных к виду, отдача полученного контента веб-серверу
     * вместе с отправляемыми заголовками
     *
     * @param
     *            App_Controller|App_Router
     * @return void
     */
    protected function _dispatch($controller)
    {
        App_Event_Observable_System::getInstance()->fire('afterRouteBeforeController');
        
        $controller->run();
        
        App_Event_Observable_System::getInstance()->fire('afterControllerBeforeTemplate');
        
        if (! $this->_response->isRedirect()) {
            $this->_response->setBody($this->_view->out());
        }
        
        App_Event_Observable_System::getInstance()->fire('afterTemplate');
        
        $this->_response->setHeader('Content-type', $this->_view->getContentType());
        
        $config = App_Application::getConfig();
        if (array_key_exists('output_in_headers', $config['errors']) && intval($config['errors']['output_in_headers']) == 1) {
            $this->_response->reportErrors();
        }
    }
}