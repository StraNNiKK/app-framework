<?php
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/View/Js.php';

class App_Server_View_Css extends App_Server_View_Js
{

    /**
     * Тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/css';
    }
}