<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Template
 * @version    $Id:$
 */

/**
 * Данный класс используется для создания объектов-шаблонов
 * и для последующей генерации видов (отображений)
 *
 * @category App
 * @package App_Template
 */
class App_Template
{

    /**
     * Результат валидации - найден ли файл шаблона или нет
     *
     * @var boolean
     */
    private $_valid = true;

    /**
     * Первоначально заданный путь к шаблону
     *
     * @var string
     */
    private $_tplPath;

    /**
     * Реальный путь к шаблону (может отличаться от первоначального
     * за счет того, что первоначальный не найден)
     *
     * @var string
     */
    private $_tplRealPath;

    /**
     * Данные, присоединяемые к виду
     *
     * @var array
     */
    private $Data = array();

    /**
     * Количество использованных шаблонов
     *
     * @static
     *
     * @var int
     */
    private static $_num = 0;

    /**
     * Массив объектов хелперов (вспомогательных функций
     * на шаблонах)
     *
     * @static
     *
     * @var array
     */
    private static $_helpers = array();

    /**
     * Путь к директории с шаблонами
     *
     * @static
     *
     * @var string
     */
    private static $_path2templates = '';

    /**
     * Путь к директории с хелперами
     *
     * @static
     *
     * @var string
     */
    private static $_path2helpers = '';

    /**
     * Имя шаблона, используемого по умолчанию
     *
     * @static
     *
     * @var string
     */
    private static $_defaultTemplate = null;

    /**
     * Возвращает валидность объекта
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->_valid;
    }

    /**
     * Инициализация основных переменных класса данными
     * из массива настроек
     *
     * @return void
     */
    protected function _init()
    {
        if (self::$_num == 0) {
            $config = App_Server::getConfig();
            self::$_path2templates = $config['templates']['path'];
            self::$_path2helpers = $config['templates']['helperPath'];
            self::$_defaultTemplate = $config['templates']['defaultTemplate'];
        }
    }

    /**
     * Конструктор.
     *
     *
     * @param string $tplPath            
     * @return void
     */
    public function __construct($tplPath)
    {
        $this->_init();
        
        /**
         * Numbering templates
         */
        self::$_num = self::$_num + 1;
        
        $this->_tplPath = (substr($tplPath, 0, 1) != '/' ? '/' . $tplPath : $tplPath);
        
        try {
            /**
             * Obtain template file name
             */
            $this->_tplRealPath = $this->getTemplateRealPath();
            if (strval($this->_tplRealPath) == '') {
                $this->_valid = false;
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $this->_valid = false;
        }
    }

    /**
     * "Волшебный метод" для перехвата обращений к хелперам из
     * шаблонов.
     *
     * @param string $method            
     * @param array $params            
     * @return mixed
     */
    public function __call($method, $params)
    {
        $helperParts = array();
        
        if (strlen($method) > 0) {
            $method = trim($method);
            $lastCharNum = 0;
            for ($i = 0; $i < strlen($method); $i ++) {
                $s = substr($method, $i, 1);
                if (ctype_upper($s)) {
                    $helperParts[] = strtolower(trim(substr($method, $lastCharNum, $i - $lastCharNum)));
                    $lastCharNum = $i;
                } else 
                    if (strlen($method) - 1 == $i) {
                        $helperParts[] = strtolower(trim(substr($method, $lastCharNum)));
                    }
            }
        }
        
        // $helperParts = explode('_', $method);
        
        $helperClass = 'App_Helper';
        $helperPath = self::$_path2helpers;
        foreach ($helperParts as $k => $v) {
            $helperClass .= '_' . ucfirst($v);
            $helperPath .= '/' . strtolower($v);
        }
        $helperPath .= '.php';
        
        if (! class_exists($helperClass)) {
            if (file_exists($helperPath)) {
                require_once $helperPath;
            } else {
                throw new Exception('Unknown helper ' . $method . '(). Can\'t find file with class ' . $helperClass);
            }
        }
        
        $helperObj = null;
        
        if (! array_key_exists($helperClass, self::$_helpers)) {
            $res = App_Loader::load($helperClass);
            if ($res) {
                $helperObj = new $helperClass();
                self::$_helpers[$helperClass] = $helperObj;
            } else {
                throw new Exception('Unknown helper ' . $method . '(). Can\'t load class ' . $helperClass);
            }
        } else {
            $helperObj = self::$_helpers[$helperClass];
        }
        
        return call_user_func_array(array(
            $helperObj,
            'run'
        ), $params);
    }

    /**
     * Метод осуществляет поиск реального пути к шаблону
     * по первоначально переданному.
     * В случае, если шаблон не был найден
     * возвращает null.
     *
     * return string|null
     */
    protected function getTemplateRealPath()
    {
        $tpl = self::$_path2templates . $this->_tplPath;
        
        if (file_exists($tpl)) {
            return $tpl;
        } else 
            if (self::$_defaultTemplate != '') {
                $pathParts = explode('/', trim($this->_tplPath, '/'));
                
                for ($i = count($pathParts) - 1; $i >= 0; $i --) {
                    $tpl = self::$_path2templates;
                    for ($j = 0; $j < $i; $j ++) {
                        $tpl .= '/' . $pathParts[$j];
                    }
                    $tpl .= '/' . self::$_defaultTemplate . '.php';
                    
                    if (file_exists($tpl)) {
                        return $tpl;
                    }
                }
            }
        
        return null;
    }

    /**
     * Метод для отображения шаблона.
     * (как результат работы генерируется строка,
     * в которой отображены все необходимые переменные, и отработали
     * все необходимые хелперы)
     *
     * @param mixed $var            
     * @return string
     */
    public function out($var = array())
    {
        if (! $this->_valid) {
            return false;
        }
        $this->Data = $var;
        
        ob_start();
        try {
            include $this->_tplRealPath;
            return ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw new Exception($e->getMessage());
        }
    }
}