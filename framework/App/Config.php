<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Config
 * @version    $Id:$
 */

/**
 * Объекты данного класса используются для работы с некими конфигурациями, т.е.
 * представляют собой ООП обертку для файлов настроек.
 *
 * @category App
 * @package App_Config
 */
class App_Config implements Countable, Iterator
{

    /**
     * Массив данных.
     * 
     * @var array
     */
    protected $_data = array();

    /**
     * Текущий элемент массива
     * 
     * @var int
     */
    protected $_arrayPosition = 0;

    /**
     *
     * @var string
     */
    protected $_keyDelimiter;

    /**
     * Допустима ли перезапись настроек
     * или же нет
     *
     * @var boolean
     */
    protected $_allowModifications;

    /**
     * Допустимые расширения файла с настройками
     *
     * @static
     *
     * @var array
     */
    static $allowedExtensions = array(
        'ini',
        'php',
        'xml'
    );

    static $configStore = array();

    /**
     * Конструктор.
     *
     * @param array $data            
     * @param string $keyDelimiter            
     * @param boolean $allowModifications            
     * @return void
     */
    public function __construct($data, $keyDelimiter = '.', $allowModifications = false)
    {
        if (! is_array($data)) {
            trigger_error("Config data isn't array", E_USER_WARNING);
            return;
        }
        
        $this->_keyDelimiter = $keyDelimiter;
        $this->_allowModifications = $allowModifications;
        
        foreach ($data as $key => $value) {
            if (strpos($key, $keyDelimiter) !== false) {
                list ($key, $subKey) = explode($keyDelimiter, $key, 2);
                $value = array(
                    $subKey => $value
                );
            }
            if (is_array($value)) {
                if (isset($this->_data[$key])) {
                    if (is_object($this->_data[$key])) {
                        $this->_data[$key]->mergeConfig(new App_Config($value, $this->_keyDelimiter, $this->_allowModifications));
                    } else {
                        trigger_error("Conflicting init values with key: $key", E_USER_WARNING);
                    }
                } else {
                    $this->_data[$key] = new App_Config($value, $this->_keyDelimiter, $this->_allowModifications);
                }
            } elseif (! isset($this->_data[$key])) {
                $this->_data[$key] = $value;
            } else {
                trigger_error("Conflicting init values with key: $key", E_USER_WARNING);
            }
        }
    }

    /**
     * Загрузить файл конфигурации.
     *
     * @static
     *
     * @param string $path            
     * @param string $keyDelimiter            
     * @param boolean $allowModifications            
     * @return App_Config
     */
    static public function loadConfig($path, $keyDelimiter = '.', $allowModifications = false)
    {
        if (array_key_exists($path, self::$configStore)) {
            return self::$configStore[$path];
        } else {
            $ext = self::detectConfigExt($path);
            
            if (self::checkExtention($ext)) {
                $ext = ucfirst($ext);
                require_once "App/Config/{$ext}.php";
                $classConfigObj = 'App_Config_' . $ext;
                self::$configStore[$path] = new $classConfigObj($path, $keyDelimiter, $allowModifications);
                return self::$configStore[$path];
            } else {
                throw new Exception('Config file (' . $path . ') has unknown extension');
            }
        }
    }

    /**
     * Проверить допустимость расширения файла конфигурации.
     *
     * @param string $ext            
     * @return boolean
     */
    static public function checkExtention($ext)
    {
        if (in_array($ext, self::$allowedExtensions)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Определить расширение файла конфигурации.
     *
     * @static
     *
     * @param string $path
     *            путь к файлу
     * @return null|string
     */
    static public function detectConfigExt($path)
    {
        if (file_exists($path)) {
            $arr = explode('.', $path);
            if (count($arr) > 0) {
                return array_pop($arr);
            } else {
                return null;
            }
        } else {
            throw new Exception("Config file (' . $path . ') isn't exists");
        }
    }

    /**
     * Выбрать ключи массива настроек.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->_data);
    }

    /**
     * Получить настройки по ключу.
     *
     * @param string $key            
     * @return mixed
     */
    public function get($key)
    {
        if (! strlen((string) $key)) {
            trigger_error("Conflicting with key: $key", E_USER_WARNING);
            return null;
        }
        if (! isset($this->_data[$key])) {
            return null;
        }
        return $this->_data[$key];
    }

    /**
     * Получить настройки по ключу.
     *
     * @param string $key            
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Задать настройку.
     *
     * Данным образом можно определять настройки
     * только в случае, если при инициализации объекта App_Config
     * был передан соотвествующий флаг для возможности модицикации объекта
     *
     * @param string $var            
     * @param mixed $value            
     * @return mixed
     */
    public function __set($var, $value)
    {
        if (! $this->_allowModifications) {
            return false;
        }
        
        if (! strlen((string) $var)) {
            trigger_error("Conflicting with key: $var", E_USER_WARNING);
            return null;
        }
        
        if (is_array($value)) {
            $value = new App_Config($value, $this->_keyDelimiter, $this->_allowModifications);
        }
        
        if (isset($this->_data[$var]) && $this->_data[$var] instanceof App_Config && $value instanceof App_Config) {
            $this->_data[$var]->mergeConfig($value);
        } else {
            $this->_data[$var] = $value;
        }
        
        return $value;
    }

    /**
     * Объединение некоего объекта конфигурации с данным
     * (замена существующих ключей новыми значениями)
     *
     * @param App_Config $config            
     * @return void
     */
    public function mergeConfig($config)
    {
        if (! is_object($config)) {
            return;
        }
        
        foreach ($config->_data as $key => $value) {
            if (! isset($this->_data[$key])) {
                $this->_data[$key] = $config->_data[$key];
            } elseif (is_object($this->_data[$key]) && is_object($value)) {
                $this->_data[$key]->mergeConfig($config->_data[$key]);
            } else {
                $this->_data[$key] = $config->_data[$key];
            }
        }
    }

    /**
     * Получить массив настроек
     *
     * @return array
     */
    public function toArray()
    {
        return $this->__toArray();
    }

    /**
     * Получить массив настроек.
     *
     * @return array
     */
    public function __toArray()
    {
        $array = $this->_data;
        foreach ($array as $k => $v) {
            if ($v instanceof App_Config) {
                $array[$k] = $v->__toArray();
            }
        }
        return $array;
    }

    /**
     * Получить количество элементов.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Получить текущий элемент.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Получить ключ текущего элемента.
     *
     * @return string
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Получить следующий элемент
     *
     * @return mixed
     */
    public function next()
    {
        ++ $this->_arrayPosition;
        next($this->_data);
    }

    /**
     * Сброс счетчика элементов.
     *
     * @return void
     */
    public function rewind()
    {
        $this->_arrayPosition = 0;
        reset($this->_data);
    }

    /**
     * Валидация объекта конфигурации.
     *
     * @return boolean
     */
    public function valid()
    {
        if ($this->_arrayPosition < count($this->_data) && $this->_arrayPosition >= 0) {
            return true;
        }
        return false;
    }
}