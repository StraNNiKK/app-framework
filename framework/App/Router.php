<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Router
 * @version    $Id:$
 */

/**
 * Абстрактный класс, который может быть унаследован любым контроллером.
 *
 * Используется в случае, когда контроллер должен сработать как роутер
 * и произвести роутинг далее в глубь по дереву директорий к следующему контроллеру.
 *
 * @category App
 * @package App_Router
 */
abstract class App_Router
{

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
     * Объект роутера
     *
     * @var App_Server_Router
     */
    protected $_router = null;

    /**
     * Конструктор
     *
     * @return void
     */
    public function __construct()
    {
        $this->_request = App_Server::getInstance()->getRequest();
        $this->_response = App_Server::getInstance()->getResponse();
        $this->_router = App_Server::getInstance()->getRouter();
    }

    /**
     * Метод в котором происходит инициализации основных объектов
     * и поиск следующего контроллера, на который производится роут.
     *
     *
     * @return void
     */
    public function run()
    {
        $pathData = $this->_request->getPathData();
        
        $defaultControllerName = strval($this->_router->getDefaultController());
        
        $controllerObj = null;
        $controllerName = null;
        $controllerKey = null;
        
        if (count($pathData) > 0) {
            $controllerKey = strtolower(array_shift($pathData));
            
            if (! $this->_request->checkPathPart($controllerKey)) {
                throw new App_Server_Router_Exception('Invalid path data');
            }
            
            $controllerName = ucfirst($controllerKey) . '_' . $this->_router->getControllerPostfix();
        } else 
            if ($defaultControllerName != '') {
                $controllerKey = $this->_router->getShortControllerName($defaultControllerName);
                $controllerKey[0] = strtolower($controllerKey[0]);
                $controllerName = $defaultControllerName;
            }
        
        $currentClass = get_class($this);
        
        if ($controllerName) {
            $currentClassParts = explode('_', $currentClass);
            $controllerClass = '';
            for ($i = 0; $i < count($currentClassParts) - 1; $i ++) {
                $controllerClass .= $currentClassParts[$i] . '_';
            }
            
            $controllerClass .= $controllerName;
            
            $res = App_Loader::load($controllerClass);
            if ($res) {
                $reflectionClassObj = new ReflectionClass($controllerClass);
                if ($reflectionClassObj->isAbstract()) {
                    throw new App_Server_Router_Exception($controllerClass . ' is abstract');
                }
                
                $controllerObj = new $controllerClass();
                
                if (method_exists($controllerObj, 'run')) {
                    $this->_request->addController($controllerKey, $controllerClass);
                    $this->_request->changePathData($pathData);
                    
                    $controllerObj->run();
                } else {
                    throw new App_Server_Router_Exception('Can\'t find ' . $controllerClass . '::run() method');
                }
            } else {
                throw new App_Server_Router_Exception('Can\'t load controller class ' . $controllerClass);
            }
        } else {
            throw new App_Server_Router_Exception('Can\'t find controller to route in class ' . $currentClass);
        }
    }
}