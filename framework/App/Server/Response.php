<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Response
 * @version    $Id:$
 */

/**
 * Абстрактный класс ответа, содержит свойства
 * и методы, характерные для всех объектов ответа.
 *
 * @category App
 * @package App_Server
 * @subpackage Response
 */
abstract class App_Server_Response
{

    /**
     * Текстовая составляющая объекта ответа
     *
     * @var string
     */
    protected $_body = '';

    /**
     * Преобразовать объект к текстовому виду
     * (равносильно получению текстовой составляющей объекта)
     *
     * @return string
     */
    public function toString()
    {
        $content = $this->getBody();
        $content = $this->__showDebug($content);
        
        return $content;
    }

    private function __showDebug($content)
    {
        $config = App_Server::getInstance()->getConfig();
        
        $ext = App_Server::getInstance()->getRequest()->getExt();
        
        if ($ext == 'php' && App_Debug::checkEnableToolbar()) {
            if (App_Debug_Toolbar::isEnabled()) {
                $debugToolbar = new App_Debug_Toolbar($config['debug']['toolbar']['data']);
                $content = $debugToolbar->decorate($content);
            }
        }
        
        return $content;
    }

    /**
     * Задать текстовую составляющую объекта ответа
     *
     * @param string $body            
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Получить текстовую составляющую объекта ответа
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }
}