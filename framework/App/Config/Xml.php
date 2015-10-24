<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Config
 * @subpackage Xml
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Config.php';


/**
 * Объекты данного класса позволяют работать с настройками,
 * хранящимися в xml файле.
 *
 * @category App
 * @package App_Config
 * @subpackage Xml
 */
class App_Config_Xml extends App_Config
{

    /**
     * Ошибка парсинга xml.
     * 
     * @var boolean
     */
    protected $_parseError = false;

    /**
     * Содержимое xml файла.
     * 
     * @var string
     */
    protected $_xml = '';

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
        $data = $this->_xmlParse($filename);
        $data = (is_array($data) ? current($data) : array());
        
        parent::__construct($data, $keyDelimiter, $allowModifications);
    }

    /**
     * Парсинг xml файла.
     *
     * @param string $filename
     *            Файл настроек
     * @return array
     */
    protected function _xmlParse($filename)
    {
        $this->_xml = file_get_contents($filename);
        return $this->createArray();
    }

    /**
     * Создать массив из xml.
     *
     * @return array
     */
    protected function createArray()
    {
        $xml = $this->_xml;
        $values = array();
        $index = array();
        $array = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);
        $i = 0;
        $name = $values[$i]['tag'];
        $array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
        $array[$name] = $this->_struct2array($values, $i);
        
        return $array;
    }

    /**
     * Преобразовать узел xml-дерева в массив.
     *
     * @param array $values            
     * @param int $i            
     * @return array
     */
    protected function _struct2array($values, &$i)
    {
        $child = array();
        if (isset($values[$i]['value'])) {
            array_push($child, $values[$i]['value']);
        }
        
        while ($i ++ < count($values)) {
            switch ($values[$i]['type']) {
                case 'cdata':
                    array_push($child, $values[$i]['value']);
                    break;
                
                case 'complete':
                    $name = $values[$i]['tag'];
                    if (! empty($name)) {
                        $child[$name] = ($values[$i]['value']) ? ($values[$i]['value']) : '';
                        if (isset($values[$i]['attributes'])) {
                            $child[$name] = $values[$i]['attributes'];
                        }
                    }
                    break;
                
                case 'open':
                    $name = $values[$i]['tag'];
                    $child[$name] = $this->_struct2array($values, $i);
                    break;
                
                case 'close':
                    return $child;
                    break;
            }
        }
        
        return $child;
    }
}