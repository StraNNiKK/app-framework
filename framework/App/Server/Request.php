<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Request
 * @version    $Id:$
 */

/**
 * Абстрактный класс запроса, содержит свойства
 * и методы, характерные для всех объектов запроса.
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
abstract class App_Server_Request implements ArrayAccess, Countable, Iterator
{

    /**
     * Массив, содержащий парметры GET, POST и данные, полученные из url
     * (пример url: /a/b/c/d, если "a" - контроллер, а "b" - метод,
     * то прибавляются параметры c, d), но без cookie
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Массив, содержащий парметры полученные из url
     * Часто изменяется в процессе роутинга
     *
     * @var array
     */
    protected $_pathData = array();

    /**
     * Все данные url в первоначальном виде
     * (то есть, если url: /a/b/c/d, то массив содержит полный набор
     * параметров: a,b,c,d)
     *
     * @var array
     */
    protected $_allPathData = array();

    /**
     * Масссив контроллеров, которые были задействованы в процессе
     * роутинга
     *
     * @var array
     */
    protected $_controllers = array();

    /**
     * Текущий экшен
     *
     * @var string
     */
    protected $_action;

    /**
     * Краткое название текущего экшена
     *
     * @var string
     */
    protected $_actionKey;

    /**
     * Текущий элемент массива
     *
     * @var int
     */
    protected $_arrayPosition = 0;

    /**
     * Конструктор.
     *
     * @param array|string $data            
     */
    public function __construct($data)
    {
        if (is_array($data)) {
            $this->_pathData = $data;
        } else {
            $tmpData = explode('/', $data);
            foreach ($tmpData as $val) {
                $res = trim($val);
                if (strpos($val, '.') !== false) {
                    $words = explode('.', $val);
                    // $res = $words[0];
                    $val = $words[0];
                }
                if (strpos($val, '-')) {
                    $words = explode('-', $val);
                    $res = $words[0];
                    for ($i = 1; $i < count($words); $i ++) {
                        $res .= ucfirst($words[$i]);
                    }
                } else {
                    $res = $val;
                }
                if ($res != '') {
                    $this->_pathData[] = $res;
                }
            }
        }
        
        $this->_allPathData = $this->_pathData;
    }
    
    /*
     * public function addFilter($filterName)
     * {
     * if (is_string($filterName)
     * && strlen($filterName) > 0
     * && !array_key_exists($filterName, $this->_filteres)) {
     * $class = 'App_Server_Request_Filter_' . ucfirst($filterName);
     * if (App_Loader::load($class)) {
     * $this->_filteres[$filterName] = new $class();
     * } else {
     * throw new Exception('Can\'t find filter class: ' . $class);
     * }
     * }
     * }
     *
     * public function removeFilter($filterName)
     * {
     * if (is_string($filterName)
     * && strlen($filterName) > 0
     * && array_key_exists($filterName, $this->_filteres)) {
     * unset($this->_filteres[$filterName]);
     * }
     * }
     *
     * public function filter($var)
     * {
     * if (is_array($var)) {
     * foreach ($var as &$v) {
     * $v = $this->filter($v);
     * }
     * } else {
     * foreach ($this->_filteres as $filterObj) {
     * $var = $filterObj->filter($var);
     * }
     * }
     *
     * return $var;
     * }
     */
    
    /**
     * Вспомогательная функция получения данных из некоего массива.
     *
     * @param array $arr            
     * @param string|int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    protected function _getFromArray($arr, $key, $filter = null, $default = null)
    {
        $value = array_key_exists($key, $arr) ? $arr[$key] : $default;
        
        switch (strtolower($filter)) {
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
            case 'numeric':
            case 'decimal':
                return (float) $value;
            case 'words':
                return preg_replace('/[^\s\w]/', '', $value);
            default:
                return $value;
        }
    }

    /**
     * Получить параметр GET/POST/url по ключу
     *
     * @param string|int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function get($key, $filter = null, $default = null)
    {
        return $this->_getFromArray($this->_params, $key, $filter, $default);
    }

    /**
     * Получить параметр GET/POST/url по ключу
     *
     * @param string|int $key            
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Получить массив параметров url
     *
     * @return array
     */
    public function getPathData()
    {
        return $this->_pathData;
    }

    /**
     * Получить массив контроллеров, участвовавших в процессе роутинга
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->_controllers;
    }

    /**
     * Получить текущий контроллер
     *
     * @return string|null
     */
    public function getController()
    {
        if (count($this->_controllers) > 0) {
            if (count($this->_controllers) == 1) {
                return $this->getUrlParam(0);
            } else {
                $ctrlKeys = array_keys($this->_controllers);
                return end($ctrlKeys);
            }
        } else {
            return null;
        }
    }

    /**
     * Получить имя текущего экшена
     *
     * @return string
     */
    public function getAction($withoutPostfix = false)
    {
        if ($withoutPostfix) {
            $postfix = App_Server::getInstance()->getRouter()->getActionPostfix();
            return substr($this->_action, 0, strlen($this->_action) - strlen($postfix));
        } else {
            return $this->_action;
        }
    }

    /**
     * Получить ключ текущего экшена (сокращенное имя)
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this->_actionKey;
    }

    /**
     * Добавить значение в массив параметров
     *
     * @param string|int $key            
     * @param mixed $val            
     * @return void
     */
    public function addParam($key, $val)
    {
        $this->_params[$key] = $val;
    }

    /**
     * Получить параметр GET/POST/url по ключу
     *
     * @param string|int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function getParam($key, $filter = null, $default = null)
    {
        $this->get($key, $filter, $default);
    }

    /**
     * Добавить массив значений к массиву параметров
     *
     * @param array $data            
     * @return void
     */
    public function addParams($data)
    {
        $this->_params = array_merge($this->_params, $data);
    }

    /**
     * Получить массив параметров GET/POST/url
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Получить массив параметров url
     *
     * @return array
     */
    public function getUrlParams()
    {
        return $this->_allPathData;
    }

    /**
     * Получить один отдельно взятый параметр url
     *
     * @param int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function getUrlParam($key, $filter = null, $default = null)
    {
        return $this->_getFromArray($this->_allPathData, $key, $filter, $default);
    }

    /**
     * Получить массив ключей массива параметров
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->_params);
    }

    /**
     * Проверяем, существует ли элемент по некоторому ключу.
     *
     * @param string|int $offset            
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_params);
    }

    /**
     * Получить параметр GET/POST/url по ключу
     *
     * @param string|int $offset            
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Добавить значение в массив параметров
     *
     * @param string|int $offset            
     * @param mixed $value            
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_params[$offset] = $value;
    }

    /**
     * Удалить значение из массива параметров
     *
     * @param string|int $offset            
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_params[$offset]);
    }

    /**
     * Посчитать количество элементов массива параметров
     *
     * @return int
     */
    public function count()
    {
        return count($this->_params);
    }

    /**
     * Получить текущий элемент массива параметров.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_params);
    }

    /**
     * Получить ключ текущего элемента массива параметров.
     *
     * @return string
     */
    public function key()
    {
        return key($this->_params);
    }

    /**
     * Получить следующий элемент из массива параметров.
     *
     * @return mixed
     */
    public function next()
    {
        ++ $this->_arrayPosition;
        next($this->_params);
    }

    /**
     * Сброс счетчика элементов массива параметров.
     *
     * @return void
     */
    public function rewind()
    {
        $this->_arrayPosition = 0;
        reset($this->_params);
    }

    /**
     * Валидация объекта конфигурации.
     *
     * @return boolean
     */
    public function valid()
    {
        if ($this->_arrayPosition < count($this->_params) && $this->_arrayPosition >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Добавить контроллер, участвующий в роутинге
     *
     * @param string $key            
     * @param string $controller            
     * @return void
     */
    public function addController($key, $controller)
    {
        $this->_controllers[$key] = $controller;
    }

    /**
     * Убрать контроллер из участвующих в роутинге
     *
     * @param string $key            
     * @return boolean
     */
    public function removeController($key)
    {
        if (array_key_exists($key, $this->_controllers)) {
            unset($this->_controllers[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Задать ключ текущего экшена (сокращенное имя)
     *
     * @param string $actionKey            
     * @return void
     */
    public function setActionKey($actionKey)
    {
        $this->_actionKey = $actionKey;
    }

    /**
     * Задать имя текущего экшена
     *
     * @param string $action            
     * @return void
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * Изменить массив параметров url
     * (этот массив участвует в роутинге)
     */
    public function changePathData(array $data)
    {
        $this->_pathData = $data;
    }

    /**
     * Проверяем правильность участка URL
     *
     * @retrun int
     */
    public function checkPathPart($path)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $path);
    }
}