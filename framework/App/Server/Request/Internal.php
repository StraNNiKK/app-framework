<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Dispatcher
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Request.php';
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/Request/Http.php';

/**
 * Класс для запроса типа internal (internal://a/b/c...)
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Request_Internal extends App_Server_Request_Http
{

    /**
     * Конструктор
     *
     * @param string $url            
     * @return void
     */
    public function __construct($url)
    {
        parent::__construct($url);
        
        parse_str($this->query, $this->_get);
        
        if (get_magic_quotes_gpc()) {
            $this->_stripSlashesRecursive($this->_get);
        }
        
        $this->_params = array_merge($this->_get, $this->_post);
    }

    /**
     * Задать входящие данные типа POST
     *
     * @param array $data            
     * @return void
     */
    public function setRawPost($data)
    {
        $this->_post = array_merge($this->_post, $data);
        $this->_params = array_merge($this->_params, $data);
    }
}
