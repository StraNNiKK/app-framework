<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Error
 * @version    $Id:$
 */

/**
 * Класс для перехвата ошибок.
 *
 * @category App
 * @package App_Server
 * @subpackage Error
 */
class App_Server_Error_Handler
{

    /**
     * Массив сообщений об ошибках
     * Статический поскольку ошибки скалываются не только от одной обработки App_Server,
     * а от всего стека объектов App_Server
     *
     * @static
     *
     * @var array
     */
    protected static $_arr = array();

    /**
     * Метод, отвечающий за перехват ошибок
     *
     * @param int $errno            
     * @param string $errstr            
     * @param string $errfile            
     * @param string $errline            
     * @return void
     */
    public function handler($errno, $errstr, $errfile, $errline)
    {
        $errorFullType = 'Error';
        $errorCategory = 'errors';
        
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errorFullType = 'Notice';
                $errorCategory = 'notices';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errorFullType = 'Warning';
                $errorCategory = 'warnings';
                break;
            case E_USER_ERROR:
                $errorFullType = "Fatal Error";
                $errorCategory = 'fatal-error';
                break;
            default:
                break;
        }
        
        $errorShort = sprintf("%s in %s on line %d", $errstr, $errfile, $errline);
        $errorFull = sprintf("PHP %s:  %s", $errorFullType, $errorShort);
        
        if (! array_key_exists($errorCategory, self::$_arr)) {
            self::$_arr[$errorCategory] = array();
        }
        
        self::$_arr[$errorCategory][] = $errorShort;
        
        if (ini_get("display_errors")) {
            printf("<br />\n<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br /><br />\n", $errorFullType, $errstr, $errfile, $errline);
        }
        
        if (ini_get('log_errors')) {
            error_log($errorFull);
        }
    }

    /**
     * Вернуть массив ошибок приложения
     *
     * @param string $type
     *            возвращаемый тип (notices | warnings | fatal-error), если не задано - то возвращаются все
     * @return array
     */
    static public function getErrors($type = null)
    {
        if (! $type || ! array_key_exists($type, self::$_arr)) {
            return self::$_arr;
        } else {
            return self::$_arr[$type];
        }
    }
}