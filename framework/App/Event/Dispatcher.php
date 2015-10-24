<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Event_Dispatcher
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Event/Observable/System.php';

/**
 * Класс отвечает за реализацию диспетчеризации событий
 * (Паттерн Наблюдатель - Observer)
 *
 * Позволяет создавать события, наблюдаемые объекты, наблюдателей
 * и вызывать события у наблюдаемых объектов (fire).
 *
 * @category App
 * @package App_Event_Dispatcher
 */
class App_Event_Dispatcher
{

    /**
     * array(
     * 'eventName' => array(
     * 0 => array(
     * 'obj' => $obj,
     * 'observers' => array(
     * 0 => array(
     * 'obj' => $objOrString,
     * 'method' => $string,
     * 'params' => $array
     * ),
     * 1 => array(...)
     * )
     * ),
     * 1 => array(...)
     * ),
     * 'eventName' => array(...)
     * )
     */
    protected static $_arrEvents = array();

    protected static $instance;

    protected $_fireMethod = 'fire';

    private function __construct()
    {}

    private function __clone()
    {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Инициализировать системные события, заданные в конфигурационном файле,
     * и назначить обработчики данных событий.
     *
     * @param string $path
     *            Путь к файлу конфигарции
     * @return void
     */
    public function initSystemConfig($path)
    {
        $configObj = App_Config::loadConfig($path);
        $configArr = $configObj->toArray();
        
        $systemConfigObj = App_Event_Observable_System::getInstance();
        
        if (count($configArr) > 0) {
            foreach ($configArr as $k => $v) {
                $this->addEvent($k, $systemConfigObj);
                if (array_key_exists('class', $v) && $v['class'] != null && array_key_exists('method', $v) && $v['method'] != null) {
                    
                    $params = array_key_exists('params', $v) && $v['params'] != null ? $v['params'] : array();
                    
                    $this->sign($k, array(
                        'object' => $v['class'],
                        'method' => $v['method'],
                        'params' => $params
                    ), $systemConfigObj);
                }
            }
        }
    }

    /**
     * Добавить событие и наблюдаемый объект, который может вызвать это событие.
     *
     * @param string $eventName
     *            Название события
     * @param object $object
     *            Наблюдаемый объект, который может сделать fire у события
     * @return void
     */
    public function addEvent($eventName, $object)
    {
        if (! array_key_exists($eventName, self::$_arrEvents)) {
            self::$_arrEvents[$eventName] = array();
        }
        
        if (! is_object($object)) {
            throw new Exception('Exception in ' . __CLASS__ . '::' . __METHOD__ . '(): the second argument should be an object');
        }
        
        $observableClassName = get_class($object);
        
        if (! method_exists($object, $this->_fireMethod)) {
            throw new Exception('Exception in ' . __CLASS__ . '::' . __METHOD__ . '(): you should create method "' . $this->_fireMethod . '" in class "' . $observableClassName . '"');
        }
        
        self::$_arrEvents[$eventName][] = array(
            'obj' => $object,
            'observers' => array()
        );
    }

    /**
     * Назначить наблюдателя за событием, которое может вызвать некий наблюдаемого объекта.
     *
     * Массив $observer должен быть следующего вида:
     * array(
     * 'object' => string|object
     * 'method' => string,
     * 'params' => array()
     * )
     *
     * $arr['object'] строка в случае статического метода или объект в случае обычного вызова $obj->meth()
     * $arr['method'] вызываемый метод
     * $arr['params'] массов параметров метода
     *
     * @param string $eventName
     *            Название события
     * @param array $observer
     *            Массив, содержащий настройки наблюдателя
     * @param object $observable
     *            Наблюдаемый объект (в случае, если не задан, наблюдатель будет наблюдать за всеми объектами, привязанными к событию)
     * @return void
     */
    public function sign($eventName, array $observer, $observable = null)
    {
        if (! array_key_exists('object', $observer) || ! array_key_exists('method', $observer)) {
            throw new Exception('Exception in ' . __CLASS__ . '::' . __METHOD__ . '(): incorrect second param (invalid array keys)');
        }
        
        $objectOrClassname = $observer['object'];
        $method = $observer['method'];
        $params = array_key_exists('params', $observer) ? $observer['params'] : array();
        
        if (! array_key_exists($eventName, self::$_arrEvents)) {
            throw new Exception('Exception in ' . __CLASS__ . '::' . __METHOD__ . '(): unknown event "' . $eventName . '"');
        }
        
        if (count(self::$_arrEvents[$eventName]) > 0) {
            if (! $observable) {
                for ($i = 0; $i < count(self::$_arrEvents[$eventName]); $i ++) {
                    if (self::$_arrEvents[$eventName][$i]['obj'] === $observable) {
                        self::$_arrEvents[$eventName][$i]['observers'][] = array(
                            'obj' => $objectOrClassname,
                            'method' => $method,
                            'params' => $params
                        );
                    }
                }
            } else {
                if (count(self::$_arrEvents[$eventName]) > 0) {
                    foreach (self::$_arrEvents[$eventName] as $kObservable => &$vObservable) {
                        $vObservable['observers'][] = array(
                            'obj' => $objectOrClassname,
                            'method' => $method,
                            'params' => $params
                        );
                    }
                }
            }
        }
    }

    /**
     * Событие произошло! Наблюдаемый объект вызвал событие!
     *
     * @param string $eventName
     *            Название события
     * @param string $observableObject
     *            Наблюдаемые объект,который вызвал событие
     * @return void
     */
    public function fire($eventName, $observableObject)
    {
        if (! array_key_exists($eventName, self::$_arrEvents)) {
            // trigger_error("Unknown event {$eventName}");
            return null;
        }
        
        if (is_object($observableObject)) {
            for ($i = 0; $i < count(self::$_arrEvents[$eventName]); $i ++) {
                if (self::$_arrEvents[$eventName][$i]['obj'] === $observableObject) {
                    $arr = self::$_arrEvents[$eventName][$i]['observers'];
                    if (count($arr) > 0) {
                        foreach ($arr as $observer) {
                            $this->_fireObserver($observer, $observableObject);
                        }
                    }
                }
            }
        } else {
            throw new Exception('Exception in ' . __CLASS__ . '::' . __METHOD__ . '(): second param isn\'t object');
        }
    }

    /**
     * Вспомогательная функция для оповещения наблюдателей.
     *
     * @param array $observer
     *            Массов настроек наблюдателя
     * @param object $observable
     *            Наблюдаемый объект
     * @return void
     */
    protected function _fireObserver(array $observer, $observable)
    {
        $params = $observer['params'];
        
        array_unshift($params, $observable);
        
        call_user_func_array(array(
            $observer['obj'],
            $observer['method']
        ), $params);
    }
}