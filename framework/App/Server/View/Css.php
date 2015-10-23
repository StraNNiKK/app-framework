<?php
require_once 'App/Server/View/Js.php';

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