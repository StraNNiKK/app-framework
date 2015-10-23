<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Response
 * @version    $Id:$
 */
require_once 'App/Server/Response.php';
require_once 'App/Server/Response/Http.php';

/**
 * Класс ответа на internal-запрос (internal://...)
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Response_Internal extends App_Server_Response_Http
{

    /**
     * Преобразование к строке
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Преобразование к строке
     *
     * @return string
     */
    public function toString()
    {
        return $this->getBody();
    }
}