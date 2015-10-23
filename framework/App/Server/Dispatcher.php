<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Dispatcher
 * @version    $Id:$
 */

/**
 * Абстрактный класс, содержащий общие для всех диспетчеров методы.
 *
 * @category App
 * @package App_Server
 * @subpackage Dispatcher
 */
abstract class App_Server_Dispatcher
{

    /**
     * Объект запроса
     *
     * @var App_Server_Request
     */
    protected $_request;

    /**
     * Объект ответа
     *
     * @var App_Server_Response
     */
    protected $_response;

    /**
     * Объект вида (отображения)
     *
     * @var App_Server_View
     */
    protected $_view;

    /**
     * Объект роутера
     *
     * @var App_Server_Router
     */
    protected $_router;

    /**
     * Получить объект запроса
     *
     * @return App_Server_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Получить объект ответа
     *
     * @return App_Server_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Получить объект вида (отображения)
     *
     * @return App_Server_View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Получить объект роутера
     *
     * @return App_Server_Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * Инициализировать объект роутера
     *
     * @return void
     */
    protected function _initRouter()
    {
        $config = App_Server::getConfig();
        if (array_key_exists('router', $config)) {
            require_once "App/Server/Router.php";
            $this->_router = new App_Server_Router($config['router']);
        } else {
            throw new Exception('You should configure route');
        }
    }

    /**
     * Инициализировать объект вида
     *
     * @return void
     */
    protected function _initView()
    {
        require_once ("App/Server/View.php");
        $this->_view = App_Server_View::getInstance($this->getRequest()->getExt());
        
        if (! is_object($this->_view)) {
            $this->getResponse()->setCode(415);
        }
    }

    /**
     * Инициализировать объект вида "по умолчанию"
     *
     * @return void
     */
    protected function _initDefaultView()
    {
        require_once ("App/Server/View.php");
        $this->_view = App_Server_View::getInstance($this->getRequest()->getDefaultExt());
        
        if (! is_object($this->_view)) {
            $this->getResponse()->setCode(415);
        }
    }
}