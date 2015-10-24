<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Config
 * @subpackage Ini
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Config.php';

/**
 * Объекты данного класса позволяют работать с настройками,
 * хранящимися в ini файле.
 *
 * @category App
 * @package App_Config
 * @subpackage Ini
 */
class App_Config_Ini extends App_Config
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
        $data = parse_ini_file($filename, true);
        
        parent::__construct($data, $keyDelimiter, $allowModifications);
    }
}