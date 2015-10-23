<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Stack
 * @version    $Id:$
 */

/**
 * Класс, отвечающий за реализацию стека объектов типа App_Server
 * (все объекты начиная со второго по счету попадают в стек при вызове
 * file_get_contents('internal://...'))
 *
 * @category App
 * @package App_Server
 * @subpackage Stack
 */
class App_Server_Stack
{

    /**
     * Сущность для реализации шаблона проектирования Singleton
     *
     * @static
     *
     * @var App_Server_Stack
     */
    private static $instance;

    /**
     * Массив объектов App_Server
     *
     * @var array
     */
    private $_stack = array();

    /**
     * Вернуть текущий объект App_Server из верхушки стека
     *
     * @static
     *
     * @return App_Server|null
     */
    public static function last()
    {
        $stack = App_Server_Stack::getInstance();
        
        return count($stack->_stack) ? $stack->_stack[count($stack->_stack) - 1] : null;
    }

    /**
     * Положить новый объект типа App_Server наверх стека
     *
     * @static
     *
     * @param App_Server $server            
     * @return int
     */
    public static function push($server)
    {
        $stack = App_Server_Stack::getInstance();
        $stack->_stack[] = $server;
        return count($stack->_stack);
    }

    /**
     * Извлечь объект из верхушки стека
     *
     * @static
     *
     * @return App_Server|null
     */
    public static function pop()
    {
        $stack = App_Server_Stack::getInstance();
        $last = $stack->_stack[count($stack->_stack) - 1];
        $last = null;
        array_pop($stack->_stack);
        return count($stack->_stack) ? $stack->_stack[count($stack->_stack) - 1] : null;
    }

    /**
     * Получить число элементов стека
     *
     * @static
     *
     * @return int
     */
    public static function size()
    {
        $stack = App_Server_Stack::getInstance();
        
        return count($stack->_stack);
    }

    /**
     * Получить сущность (Singleton pattern)
     *
     * @static
     *
     * @return App_Server
     */
    public static function getInstance()
    {
        if (! is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Конструктор.
     *
     * @return void
     */
    private function __construct()
    {}
}