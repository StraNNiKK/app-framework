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
require_once APP_FRAMEWORK_MAIN_DIR . 'Server/View.php';


/**
 * Отображение в ответ на запрос php-скрипта.
 *
 * @category App
 * @package App_Server
 * @subpackage View
 */
class App_Server_View_Php extends App_Server_View
{

    /**
     * Определить тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        $type = 'text/html';
        $config = App_Server::getConfig();
        
        $charset = $config['response']['defaultCharset'];
        
        if (strlen($charset)) {
            $type .= "; charset={$charset}";
        }
        
        return $type;
    }

    /**
     * Поиск подходящего шаблона в зависимости от запроса,
     * поиск подходящего layout-шаблона, комбинирование шаблонов и
     * подстановка в них переданных из контроллера данных, возврат контента
     * в виде html
     *
     * @return string
     */
    public function out()
    {
        $template = $this->_getContentTemplate();
        $content = $template->out($this->_data);
        $layout = $this->_getLayoutTemplate();
        
        if ($layout->isValid()) {
            $this->_layoutData['content'] = $content;
            $content = $layout->out($this->_layoutData);
        }
        
        return $content;
    }

    /**
     * Создание объекта App_Template для отображения (вида)
     *
     * @return App_Template
     */
    public function _getContentTemplate()
    {
        return $this->_getTemplate('views');
    }

    /**
     * Создание объекта App_Template для макета страницы
     *
     * @return App_Template
     */
    public function _getLayoutTemplate()
    {
        return $this->_getTemplate('layouts');
    }

    /**
     * Создать объект App_Template в зависимости от типа шаблона
     *
     * @param string $type            
     * @return App_Template
     */
    protected function _getTemplate($type = 'views')
    {
        $request = App_Server::getRequest();
        
        $controllers = $request->getControllers();
        $action = $request->getActionKey();
        $ext = $request->getExt();
        $params = $request->getPathData();
        
        $first = true;
        $templateName = '';
        
        foreach ($controllers as $key => $ctrl) {
            if ($first) {
                $templateName = '/' . $type . '/';
                $templateName .= ($type == 'layouts' ? ($ext . '/' . $key) : ($key . '/' . $ext));
                
                $first = false;
            } else {
                $templateName .= '/' . $key;
            }
        }
        
        $templateName .= '/' . $action;
        
        if (count($params) > 0) {
            foreach ($params as $k => $v) {
                $templateName .= '/' . $v;
            }
        }
        
        $templateName .= '.php';
        
        require_once 'App/Template.php';
        $template = new App_Template($templateName);
        
        return $template;
    }
}
