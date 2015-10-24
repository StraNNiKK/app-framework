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
 * Отображение в ответ на запрос html-страницы.
 *
 * @category App
 * @package App_Server
 * @subpackage View
 */
class App_Server_View_Html extends App_Server_View_Php
{

    public function out()
    {
        $template = $this->_getContentTemplate();
        
        if (! $template->isValid() && ! $this->checkAssigned()) {
            $this->generateError();
        }
        
        $content = $template->out($this->_data);
        $layout = $this->_getLayoutTemplate();
        
        if ($layout->isValid()) {
            $this->_layoutData['content'] = $content;
            $content = $layout->out($this->_layoutData);
        }
        
        return $content;
    }
}