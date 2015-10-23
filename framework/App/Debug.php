<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Debug
 * @version    $Id:$
 */

/**
 * Класс, содержащий методы для отладки и отображения данных в процессе разработки.
 *
 * @category App
 * @package App_Debug
 */
class App_Debug
{

    /**
     * Переменная, в которой фиксируется
     * разрешение на отладку (в случае, если режим отладки отключен,
     * не будет отображаться результат функции d()
     * и debug toolbar)
     *
     * @static
     *
     * @var boolean
     */
    protected static $_allow = false;

    /**
     * Отображать ли debug toolbar для
     * вывода отладочной информации
     *
     * @static
     *
     * @var boolean
     */
    protected static $_showToolbar = false;

    protected static $_logs = array();

    /**
     * Метод для отображения информации об обьекте
     * равносильно var_dump(), но в более читабельном варианте.
     *
     * @static
     *
     * @param mixed $var            
     * @param boolean $echo            
     * @return string
     */
    public static function show($var, $echo = true)
    {
        if (! App_Debug::checkAllow()) {
            return null;
        }
        
        // var_dump the variable into a buffer and keep the output
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        
        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        
        $output = htmlspecialchars($output, ENT_QUOTES);
        
        $output = '<pre>' . $output . '</pre>';
        
        if ($echo) {
            echo ($output);
        }
        
        return $output;
    }

    public static function log($something)
    {
        if (! App_Debug::checkAllow()) {
            return null;
        }
        
        if (! is_string($something)) {
            $something = self::show($something, false);
        }
        
        self::$_logs[] = $something;
        
        return true;
    }

    public static function getLog()
    {
        return self::$_logs;
    }

    /**
     * Разрешить режим отладки
     *
     * @static
     *
     * @param boolean $allow            
     * @return void
     */
    public static function setAllow($allow)
    {
        self::$_allow = (bool) $allow;
    }

    /**
     * Проверить режим отладки
     *
     * @static
     *
     * @return boolean
     */
    public static function checkAllow()
    {
        return self::$_allow;
    }

    /**
     * Активировать debug toolbar
     *
     * @static
     *
     * @param boolean $enable            
     * @return void
     */
    public static function enableToolbar($enable)
    {
        self::$_showToolbar = (bool) $enable;
    }

    /**
     * Проверить, активирован ли debug toolbar
     *
     * @static
     *
     * @return boolean
     */
    public static function checkEnableToolbar()
    {
        return (bool) self::$_showToolbar;
    }
}