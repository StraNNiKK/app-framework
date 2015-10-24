<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Router
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Router/Exception.php';

/**
 * Класс первичного роутера.
 * С объекта первичного роутера начинается процесс роутинга
 * и помимо этого объект данного типа содержит методы, которые используются
 * повсеместо в ходе данного процесса.
 *
 * @category App
 * @package App_Server
 * @subpackage Router
 */
class App_Server_Router
{

    /**
     * Массив соответствий путей роутинга и классов контроллеров
     *
     * @var array
     */
    protected $_set = array();

    /**
     * Массив настроек роутера
     *
     * @var array
     */
    protected $_settings = array();

    /**
     * Флаг, указывающий на то, что вместо текущей старницы
     * должна быть выведена страница об ошибке
     *
     * @var boolean
     */
    protected $_routeErrorPage = false;

    /**
     * Конструктор.
     *
     * @param array $settings            
     * @return void
     */
    public function __construct($settings)
    {
        $this->_settings = $settings;
        
        if (array_key_exists('config', $settings)) {
            $configObj = App_Config::loadConfig($settings['config']);
            $configArr = $configObj->toArray();
            
            if (count($configArr) > 0) {
                foreach ($configArr as $k => $v) {
                    $this->set($k, $v);
                }
            }
            
            $defaultController = $this->getDefaultController();
            if ($defaultController) {
                $this->set('index', $defaultController);
            }
        }
    }

    /**
     * Задать соотвествие /путь/ : класс роутера или же контроллера
     *
     * @param string $path            
     * @param string $ctrl            
     * @return void
     */
    public function set($path, $ctrl)
    {
        $path = '/' . trim($path, '/');
        $this->_set[$path] = $ctrl;
    }

    /**
     * Получить массив настроек роутера
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Получить постфикс класса контроллера
     *
     * @return string|null
     */
    public function getControllerPostfix()
    {
        if (array_key_exists('controllerPostfix', $this->_settings)) {
            return $this->_settings['controllerPostfix'];
        } else {
            return null;
        }
    }

    /**
     * Получить постфикс экшена
     *
     * @return string|null
     */
    public function getActionPostfix()
    {
        if (array_key_exists('actionPostfix', $this->_settings)) {
            return $this->_settings['actionPostfix'];
        } else {
            return null;
        }
    }

    /**
     * Получить имя контроллера по-умолчанию
     *
     * @return string|null
     */
    public function getDefaultController()
    {
        if (array_key_exists('defaultController', $this->_settings)) {
            return ucfirst($this->_settings['defaultController']) . '_' . $this->getControllerPostfix();
        } else {
            return null;
        }
    }

    /**
     * Получить имя экшена по-умолчанию
     *
     * @return string|null
     */
    public function getDefaultAction()
    {
        if (array_key_exists('defaultAction', $this->_settings)) {
            return $this->_settings['defaultAction'] . $this->getActionPostfix();
        } else {
            return null;
        }
    }

    /**
     * Получить имя контроллера, соотвествующего странице об ошибке
     *
     * @param string|int $errNum            
     * @return string|null
     */
    public function getErrorController($errNum = null)
    {
        $errNum = ($errNum ? $errNum : $this->_routeErrorPage);
        if ($errNum && array_key_exists($errNum, $this->_settings) && array_key_exists('defaultController', $this->_settings[$errNum])) {
            return ucfirst($this->_settings[$errNum]['defaultController']) . '_' . $this->getControllerPostfix();
        } else {
            return null;
        }
    }

    /**
     * Получить имя экшена, соотвествующего странице об ошибке
     *
     * @param string|int $errNum            
     * @return string|null
     */
    public function getErrorAction($errNum = null)
    {
        $errNum = ($errNum ? $errNum : $this->_routeErrorPage);
        if ($errNum && array_key_exists($errNum, $this->_settings) && array_key_exists('defaultAction', $this->_settings[$errNum])) {
            return $this->_settings[$errNum]['defaultAction'] . $this->getActionPostfix();
        } else {
            return null;
        }
    }

    /**
     * Получить имя контроллера без постфикса.
     *
     * @return string|null
     */
    public function getShortControllerName($controller)
    {
        $controller = substr($controller, 0, strlen($controller) - strlen($this->getControllerPostfix()) - 1);
        return $controller;
    }

    /**
     * Получить имя экшена без постфикса.
     *
     * @return string|null
     */
    public function getShortActionName($action)
    {
        $action = substr($action, 0, strlen($action) - strlen($this->getActionPostfix()));
        return $action;
    }

    /**
     * Найти контроллер по параметрам url в массиве соответсвий
     *
     * @param array $pathData            
     * @return array
     */
    protected function _findControllerInConfig($pathData)
    {
        $foundController = null;
        $controllerData = array();
        
        $checkedPathes = array();
        
        // if there is some url params like /param1/param2/param3
        if (count($pathData) > 0) {
            // find mathes for all variants of params ( /param1/param2/param3 /param1/param2 /param1 )
            // in array with route configs
            for ($i = count($pathData); $i > 0; $i --) {
                
                $tmpPath = '';
                for ($j = 0; $j < $i; $j ++) {
                    // check path part
                    if (! in_array($pathData[$j], $checkedPathes)) {
                        if (! App_Server::getInstance()->getRequest()->checkPathPart($pathData[$j])) {
                            throw new App_Server_Router_Exception('Invalid path data');
                        } else {
                            $checkedPathes[] = $pathData[$j];
                        }
                    }
                    $tmpPath .= '/' . $pathData[$j];
                }
                // find matches for some url (e.p. /param1/param2 in config)
                foreach ($this->_set as $path => $controller) {
                    $pos = strpos($path, $tmpPath);
                    if ($pos !== false && $pos == 0) {
                        $foundController = $controller;
                        for ($k = $i; $k < count($pathData); $k ++) {
                            $controllerData[] = $pathData[$k];
                        }
                        break (2);
                    }
                }
            }
        } else {
            // find matches for '/' url
            if (array_key_exists('/', $this->_set)) {
                $foundController = $this->_set['/'];
            } else {
                $foundController = $this->getDefaultController();
            }
        }
        
        return array(
            'controller' => $foundController,
            'pathData' => $controllerData
        );
    }

    /**
     * Осуществить первоначальный роут до первого контроллера.
     * В ходе роутинга если искомый контроллер не был найден, то
     * генерируется исключение
     *
     * @return App_Controller|App_Router
     */
    public function route()
    {
        $request = App_Server::getInstance()->getRequest();
        $data = $this->_findControllerInConfig($request->getPathData());
        
        $controllerClass = $data['controller'];
        $controllerObj = null;
        
        if ($controllerClass != null) {
            $res = App_Loader::load($controllerClass);
            if ($res) {
                $controllerObj = new $controllerClass();
                
                if (method_exists($controllerObj, 'run')) {
                    $request->addController(strtolower($controllerClass), $controllerClass);
                    $request->changePathData($data['pathData']);
                    
                    return $controllerObj;
                } else {
                    throw new App_Server_Router_Exception('Can\'t find ' . $controllerClass . '::run() method');
                }
            } else {
                throw new App_Server_Router_Exception('Can\'t load class: ' . $controllerClass);
            }
        } else {
            throw new App_Server_Router_Exception('Can\'t find controller in route config');
        }
    }

    /**
     * Осуществить первоначальный роутинг до первого контроллера,
     * отвечающего за генерацию страницы с ошибкой
     *
     * @param Exception $errObj            
     * @param string|int $errNum            
     * @return App_Controller|App_Router
     */
    public function route2errorPage($errObj, $errNum)
    {
        $defaultErrorController = $this->getErrorController($errNum);
        $defaultErrorAction = $this->getErrorAction($errNum);
        
        if (! $defaultErrorController || ! $defaultErrorAction) {
            throw new App_Server_Router_Exception('There is no settings for error page controller/action');
        }
        
        $this->_routeErrorPage = $errNum;
        
        $request = App_Server::getInstance()->getRequest();
        $request->addParam('_exception', $errObj);
        
        $controllers = array_reverse($request->getControllers());
        
        $defaultErrorControllerKey = $this->getShortControllerName($defaultErrorController);
        $defaultErrorControllerKey[0] = strtolower($defaultErrorControllerKey[0]);
        
        $controllerObj = null;
        
        if (count($controllers) > 0) {
            foreach ($controllers as $key => $errorControllerName) {
                $errorControllerClass = $this->getShortControllerName($errorControllerName) . '_' . $defaultErrorController;
                $res = App_Loader::load($errorControllerClass);
                if ($res) {
                    $controllerObj = new $errorControllerClass();
                    if (method_exists($controllerObj, 'run')) {
                        $request->addController($defaultErrorControllerKey, $errorControllerClass);
                        
                        return $controllerObj;
                    } else {
                        throw new App_Server_Router_Exception('Can\'t find ' . $errorControllerClass . '::run() method');
                    }
                } else {
                    $request->removeController($key);
                }
            }
        }
        
        $res = App_Loader::load($defaultErrorController);
        if ($res) {
            $controllerObj = new $defaultErrorController();
            if (method_exists($controllerObj, 'run')) {
                $request->addController(strtolower($defaultErrorController), $defaultErrorController);
                
                return $controllerObj;
            } else {
                throw new App_Server_Router_Exception('Can\'t find ' . $defaultErrorController . '::run() method');
            }
        } else {
            throw new App_Server_Router_Exception('Can\'t find error controller');
        }
    }

    /**
     * Определить - роутится обычная страница или же страница для вывода ошибки
     *
     * @return bool
     */
    public function isRoute2errorPage()
    {
        return (bool) $this->_routeErrorPage;
    }

    /**
     * Найти среди заданных путей url первоначальный путь для
     * заданного класса контроллера
     *
     * @param string $controllerClass            
     * @return string|boolean без '/' на концах
     */
    public function backRoute($controllerClass)
    {
        if (count($this->_set) > 0) {
            foreach ($this->_set as $path => $control) {
                if (strtolower($control) == strtolower($controllerClass)) {
                    return $path;
                }
            }
        }
        
        return false;
    }

    /**
     * Найти все пути url, заданные для некоего класса контроллера
     *
     * @param string $controllerClass            
     * @return array массив путей (строк), каждый из которых без '/' на концах
     */
    public function backRouteAll($controllerClass)
    {
        $found = array();
        
        if (count($this->_set) > 0) {
            foreach ($this->_set as $path => $control) {
                if (strtolower($control) == strtolower($controllerClass)) {
                    $found[] = $path;
                }
            }
        }
        
        return $found;
    }
}