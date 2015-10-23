<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage View
 * @version    $Id:$
 */

/**
 * Абстрактный класс вида (отображения), содержит свойства и
 * методы, характерные для всех объектов вида
 *
 * @category App
 * @package App_Server
 * @subpackage View
 */
abstract class App_Server_View
{

    /**
     * Массив данных, получаемых от контроллера и прикрепляемых к виду
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Данные были прикреплены к виду
     *
     * @var bool
     */
    protected $_assigned = false;

    /**
     * Массив данных, предназначенных для layout (макета)
     *
     * @var array
     */
    protected $_layoutData = array();

    /**
     * Контент, генерируемый видом
     *
     * @var string
     */
    protected $_out;

    /**
     * Присоединить данные к виду.
     *
     * @param string|int $key            
     * @param mixed $var            
     * @return void
     */
    public function assign($key, $var)
    {
        $this->_assigned = true;
        
        $this->_data[$key] = $var;
    }

    /**
     * Присоединить массив данных к виду
     *
     * @param array $arr            
     * @return void
     */
    public function assignArray($arr)
    {
        $this->_assigned = true;
        
        if (is_array($arr)) {
            $this->_data = array_merge($this->_data, $arr);
        } else {
            $this->_data = $arr;
        }
    }

    /**
     * Проверить, были ли данные прикреплены к виду
     *
     * @return bool
     */
    public function checkAssigned()
    {
        return $this->_assigned;
    }

    /**
     * Присоединить данные к виду.
     *
     * @param string|int $key            
     * @param mixed $var            
     * @return void
     */
    public function assignLayout($key, $var)
    {
        $this->_layoutData[$key] = $var;
    }

    /**
     * Присоединить массив данных к виду
     *
     * @param array $arr            
     * @return void
     */
    public function assignArrayLayout($arr)
    {
        $this->_layoutData = array_merge($this->_layoutData, $arr);
    }

    /**
     * Получить контент, сгенеренный видом
     *
     * @return string
     */
    public function out()
    {
        return $this->_out;
    }

    /**
     * Получить объект вида в зависимости от заданного расширения
     * (нет Singleton)
     *
     * @static
     *
     * @param string $type            
     * @return App_Server_View|false
     */
    public static function getInstance($type)
    {
        if (($class = App_Server_View::getViewClass($type)) !== false) {
            $ret = new $class();
        } else {
            return false;
        }
        return $ret;
    }

    /**
     * Найти и подгрузить класс в зависимости от расширения
     *
     * @static
     *
     * @param string $type            
     * @return App_Server_View|false
     */
    public static function getViewClass($type)
    {
        $class = 'App_Server_View_' . ucfirst($type);
        
        if (App_Loader::find($class)) {
            return $class;
        } else {
            self::generateError();
            
            // $request = App_Server::getInstance()->getRequest();
            // $request->setDefaultExt();
            
            // $class = 'App_Server_View_' . ucfirst($request->getDefaultExt());
            
            // return $class;
        }
    }

    /**
     * Проверить, существует ли отображение для данного расширения
     * или же нет
     *
     * @static
     *
     * @param string $type            
     * @return boolean
     */
    public static function isValidView($type)
    {
        return (App_Server_View::getViewClass($type) !== false) ? true : false;
    }

    /**
     * Получить заголовок Content-Type
     * 
     * @ignore
     *
     */
    abstract public function getContentType();

    static public function generateError()
    {
        throw new Exception('Unknown Ext');
    }
}