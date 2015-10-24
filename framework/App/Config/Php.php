<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Config
 * @subpackage Php
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Config.php';

/**
 * Объекты данного класса позволяют работать с настройками,
 * хранящимися в php файле.
 *
 * @category App
 * @package App_Config
 * @subpackage Php
 */
class App_Config_Php extends App_Config
{

    /**
     * Конструктор.
     *
     * @param string $filename
     *            Файл настроек
     * @param string $keyDelimiter
     *            Разделитель настроек
     * @param boolean $allowModifications
     *            Допустимость модификации объекта конфигурации
     * @return void
     */
    public function __construct($filename, $keyDelimiter = '.', $allowModifications = false)
    {
        $data = include ($filename);
        
        parent::__construct($data, $keyDelimiter, $allowModifications);
    }
}