<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Router
 * @version    $Id:$
 */

/**
 * Класс исключений, генерируемых при ошибках роутинга
 *
 * @category App
 * @package App_Server
 * @subpackage Router
 */
class App_Server_Router_Exception extends Exception
{

    /**
     * Конструктор.
     *
     * @param string $errstr            
     * @param int $errno            
     * @return void
     */
    public function __construct($errstr, $errno = null)
    {
        $request = App_Server::getRequest();
        $errstr = $errstr . ' [request path: ' . $request->getPath() . ']';
        
        parent::__construct($errstr, $errno);
    }
}