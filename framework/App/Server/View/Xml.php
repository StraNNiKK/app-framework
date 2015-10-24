<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage View
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/View/Php.php';


/**
 * Отображение в ответ на запрос xml.
 *
 * @category App
 * @package App_Server
 * @subpackage View
 */
class App_Server_View_Xml extends App_Server_View_Php
{

    /**
     * Тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/xml; charset=utf-8';
    }

    /**
     * Поиск подходящего шаблона в зависимости от запроса,
     * подстановка в него переданных из контроллера данных, возврат контента,
     * либо же если шаблон не найден генерация стандартного xml судя по
     * переданным из контроллера данным
     *
     * @return string
     */
    public function out()
    {
        $template = $this->_getContentTemplate();
        
        if ($template->isValid()) {
            $content = $template->out($this->_data);
        } else {
            if ($this->checkAssigned()) {
                $content = $this->toXml($this->_data);
            } else {
                $this->generateError();
            }
        }
        return $content;
    }

    /**
     * Функция для конвертации массива php в строку XML
     *
     * @param array $data            
     * @param string $rootNodeName
     *            корневой узел.
     * @param SimpleXMLElement $xml
     *            для рекурсивного вызова метода
     * @return string
     */
    public function toXml($data, $rootNodeName = 'data', $xml = null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        
        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }
        
        // loop through the data passed in.
        foreach ($data as $key => $value) {
            // no numeric keys in our xml please!
            if (is_numeric($key)) {
                // make string key...
                $key = "unknownNode_" . (string) $key;
            }
            
            // replace anything not alpha numeric
            $key = preg_replace('/[^a-z]/i', '', $key);
            
            // if there is another array found recrusively call this function
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // recrusive call.
                $this->toXml($value, $rootNodeName, $node);
            } else {
                // add single node.
                $value = htmlentities($value);
                $xml->addChild($key, $value);
            }
        }
        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }
}