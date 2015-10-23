<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Controller
 * @version    $Id:$
 */

/**
 * Абстрактный класс, который может быть унаследован любым контроллером.
 *
 * Содержит ряд методов, которые должны присутствовать в каждом контроллере.
 *
 * @category App
 * @package App_Controller
 */
abstract class App_Controller
{

    /**
     * Массив данных, которые передаются в отображение
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Массив данных, которые передаются в макет
     *
     * @var array
     */
    protected $_layoutData = array();

    /**
     * Объект запроса.
     *
     * @var App_Server_Request
     */
    protected $_request = null;

    /**
     * Объект ответа.
     *
     * @var App_Server_Response
     */
    protected $_response = null;

    /**
     * Объект вида (отображения)
     *
     * @var App_Server_View
     */
    protected $_view = null;

    /**
     * Объект роутера
     *
     * @var App_Server_Router
     */
    protected $_router = null;

    /**
     * Показатель, был ли произведен редирект
     *
     * @var boolean
     */
    protected $_redirected = false;

    /**
     * Конструктор
     *
     * @return void
     */
    public function __construct()
    {
        $this->_router = App_Server::getInstance()->getRouter();
        $this->_request = App_Server::getInstance()->getRequest();
        $this->_response = App_Server::getInstance()->getResponse();
        $this->_view = App_Server::getInstance()->getView();
    }

    /**
     * Метод в котором происходит инициализации основных объектов контроллера и
     * выбор - обрабатывать обычный экшен или же экшен сообщения об ошибке.
     *
     * @return void
     */
    public function run()
    {
        if ($this->_router->isRoute2errorPage()) {
            $this->_runErrorAction();
        } else {
            $this->_runAction();
        }
    }

    /**
     * Метод для запуска обычного экшена.
     *
     * Ищется экшен из парметров урла, если такового не находится,
     * то берется экшен, заданный по умолчанию в настройках.
     * Если ни один из них не задан, то генерируется исключение.
     *
     * @return void
     */
    protected function _runAction()
    {
        $action = strval($this->_router->getDefaultAction());
        $actionKey = $this->_router->getShortActionName($action);
        
        $pathData = $this->_request->getPathData();
        
        if (count($pathData) > 0) {
            $actionKey = array_shift($pathData);
            
            if (! $this->_request->checkPathPart($actionKey)) {
                throw new App_Server_Router_Exception('Invalid path data');
            }
            
            $action = $actionKey . $this->_router->getActionPostfix();
        }
        
        if ($action) {
            $exists = method_exists($this, $action);
            if ($exists) {
                $this->_request->setActionKey($actionKey);
                $this->_request->setAction($action);
                $this->_request->changePathData($pathData);
                $this->_request->addParams($pathData);
                
                if (method_exists($this, '_init')) {
                    $this->_init();
                }
                
                if (! $this->_redirected) {
                    $this->$action();
                }
                
                if (count($this->_data) > 0) {
                    $this->_view->assignArray($this->_data);
                }
                
                if (count($this->_layoutData) > 0) {
                    $this->_view->assignArrayLayout($this->_layoutData);
                }
            } else {
                throw new App_Server_Router_Exception('Can\'t find action ' . get_class($this) . '::' . $action . '()');
            }
        } else {
            throw new App_Server_Router_Exception('Can\'t find any action to do in ' . get_class($this));
        }
    }

    /**
     * Метод для запуска экшена, соответствующего одной
     * из страниц с ошбиками (404 || 500)
     *
     * @return void
     */
    protected function _runErrorAction()
    {
        $routerObj = App_Server::getInstance()->getRouter();
        
        $action = strval($routerObj->getErrorAction());
        $actionKey = $this->_router->getShortActionName($action);
        
        $exists = method_exists($this, $action);
        if ($exists) {
            $this->_request->setActionKey($actionKey);
            $this->_request->setAction($action);
            
            if (method_exists($this, '_init')) {
                $this->_init();
            }
            
            if (! $this->_redirected) {
                $this->$action();
            }
            $this->_view->assign('exception', $this->_request->_exception);
        } else {
            throw new App_Server_Router_Exception('Can\'t find action ' . get_class($this) . '::' . $action . '()');
        }
    }

    /**
     * Метод, запускаемый из экшена, для генерации страницы с ошибкой 404
     *
     * @return void
     */
    public function show404page()
    {
        throw new App_Server_Router_Exception('User exit from action ' . __METHOD__);
    }

    /**
     * Метод, запускаемый из экшена, для генерации страницы с ошибкой 500
     *
     * @return void
     */
    public function show500page()
    {
        throw new App_Server_Router_Exception('User exit from action ' . __METHOD__);
    }

    /**
     * Присоединить переменную к виду.
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function assignView($key, $value)
    {
        $this->_view->assign($key, $value);
    }

    /**
     * Присоединить переменную к виду.
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function assignLayout($key, $value)
    {
        $this->_view->assignLayout($key, $value);
    }

    /**
     * Метод, запускаемый из экшена, для редиректа на другую страницу.
     *
     * @param string $location            
     * @param boolean $permanent            
     * @param boolean $dontThrowException            
     * @return void
     */
    public function redirect($location, $permanent = false, $dontThrowException = false)
    {
        $this->_redirected = true;
        $this->_response->redirect($location, $permanent, $dontThrowException);
    }
}