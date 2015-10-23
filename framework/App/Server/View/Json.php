<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage View
 * @version    $Id:$
 */
require_once 'App/Server/View/Php.php';

/**
 * Отображение в ответ на запрос json.
 *
 * @category App
 * @package App_Server
 * @subpackage View
 */
class App_Server_View_Json extends App_Server_View_Php
{

    /**
     * Тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        return 'x-application/json; charset=utf-8';
    }

    /**
     * Поиск подходящего шаблона в зависимости от запроса,
     * подстановка в него переданных из контроллера данных, возврат контента,
     * либо же если шаблон не найден генерация стандартного json судя по
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
                $content = json_encode($this->_data);
            } else {
                $this->generateError();
            }
        }
        
        return $content;
    }
}