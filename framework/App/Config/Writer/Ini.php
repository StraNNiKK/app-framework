<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Config
 * @subpackage Writer
 * @version    $Id:$
 */
require_once 'App/Config.php';
require_once 'App/Config/Ini.php';

/**
 * Объекты данного класса используются для перезаписи исходных
 * настроек, хранящихся в ini файлах
 *
 * @category App
 * @package App_Config
 * @subpackage Writer
 */
class App_Config_Writer_Ini
{

    /**
     *
     * @var string
     */
    protected $_keyDelimiter = '.';

    /**
     *
     * @var string
     */
    protected $_filename;

    /**
     * Метод для перезаписи файла.
     *
     * @param string $filename            
     * @param App_Config $config            
     * @return boolean
     */
    public function write($filename = null, App_Config $config = null)
    {
        if (! is_null($filename)) {
            $this->setFilename($filename);
        }
        if (! is_null($config)) {
            $this->setConfig($config);
        }
        
        if (is_null($this->_config) || ! ($this->_config instanceof App_Config)) {
            throw new Exception('Invalid config object');
        }
        
        $iniString = '';
        $sectionsPart = '';
        foreach ($this->_config as $key => $value) {
            if ($value instanceof App_Config && count($value)) {
                $sectionsPart .= "[$key]\n";
                $sectionsPart .= $this->_config2string($value);
            } else {
                if (is_null($value))
                    continue;
                if ($value === true)
                    $value = 'On';
                elseif ($value === false)
                    $value = 'Off';
                elseif (preg_match('/[^a-zA-Z0-9 ]/', $value))
                    $value = "\"$value\"";
                $iniString .= "$key = $value\n";
            }
        }
        
        $iniString .= "\n" . $sectionsPart;
        
        return (bool) file_put_contents($this->_filename, $iniString);
    }

    /**
     * Задать разделитель настроек.
     *
     * @param string $keyDelimiter            
     * @return void
     */
    public function setKeyDelimiter($keyDelimiter)
    {
        $this->_keyDelimiter = $keyDelimiter;
    }

    /**
     * Задать конфиг для перезаписи.
     *
     * @param App_Config $config            
     * @return void
     */
    public function setConfig(App_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Задать имя файла конфигурации.
     *
     * @param string $filename            
     * @return void
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }

    /**
     * Преобразовать объект конфигурации в строку
     * (к виду, схожему к тому, как эта конфигурация содержится
     * в исходном текстовом файле).
     *
     * @param App_Config $config            
     * @param string $prefix            
     * @return string
     */
    protected function _config2string($config, $prefix = '')
    {
        $string = '';
        foreach ($config as $key => $value) {
            if ($value instanceof App_Config && count($value)) {
                $string .= $this->_config2string($value, $prefix . $key . $this->_keyDelimiter) . "\n";
            } else {
                if ($value === true)
                    $value = 'On';
                elseif ($value === false)
                    $value = 'Off';
                elseif (preg_match('/[^a-zA-Z0-9 ]/', $value))
                    $value = "\"$value\"";
                $string .= $prefix . $key . " = $value\n";
            }
        }
        return $string;
    }
}