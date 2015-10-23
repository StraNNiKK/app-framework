<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Request
 * @version    $Id:$
 */
require_once 'App/Server/Request.php';

/**
 * Класс обычного http-запроса
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Request_Http extends App_Server_Request
{

    /**
     * Массив входящих параметров GET
     *
     * @var array
     */
    protected $_get = array();

    /**
     * Массив входящих параметров POST
     *
     * @var array
     */
    protected $_post = array();

    /**
     * Массив полученных Cookie
     *
     * @var array
     */
    protected $_cookie = array();

    /**
     * Массив составляющих url
     *
     * @var array
     */
    protected $_uri = array();

    /**
     * Запрошенное расширение
     *
     * @var string
     */
    protected $_ext = null;

    /**
     * Расширение "по умолчанию"
     *
     * @var string
     */
    protected $_defaultExt = 'php';

    /**
     * Массив пользовательских заголовков
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * Запрашиваемый протокол
     *
     * @var string
     */
    protected $scheme = 'http';

    /**
     * Часть url: хост
     *
     * @var string
     */
    protected $host;

    /**
     * Часть url: порт
     *
     * @var int
     */
    protected $port = 80;

    /**
     * Часть url: пользователь
     *
     * @var string
     */
    protected $user;

    /**
     * Часть url: пароль
     *
     * @var string
     */
    protected $pass;

    /**
     * Часть url: путь (например /a/b/c/d)
     */
    protected $path;

    /**
     * Часть url: параметры (например ?a=1&b=2)
     */
    protected $query;

    /**
     * Конструктор
     *
     * @param string $url            
     * @return void
     */
    public function __construct($url = null)
    {
        $this->_prepareUriData($url);
        $this->_prepareHeaders();
        $this->_prepareRequestData();
        
        parent::__construct($this->_uri['path']);
    }

    /**
     * Получить метод передачи данных
     *
     * @return string|null
     */
    public function getMethod()
    {
        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return $_SERVER['REQUEST_METHOD'];
        } else {
            return null;
        }
    }

    /**
     * Определить является ли запрос POST или нет
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }
        
        return false;
    }

    /**
     * Определить является ли запрос GET или нет
     *
     * @return boolean
     */
    public function isGet()
    {
        if ('GET' == $this->getMethod()) {
            return true;
        }
        
        return false;
    }

    /**
     * Парсинг URL
     *
     * @param string $uri            
     * @return void
     */
    protected function _prepareUriData($uri = null)
    {
        $this->_uri = parse_url($uri == null ? $_SERVER['REQUEST_URI'] : $uri);
        
        foreach ($this->_uri as $name => $value) {
            $this->$name = $value;
        }
        
        if (isset($this->path)) {
            $this->path = preg_replace('/[\/\/]{2,}/', '/', $this->path);
        }
        
        if (! isset($this->host)) {
            $this->host = $_SERVER['HTTP_HOST'];
        }
        
        if (! isset($this->port)) {
            $this->port = $_SERVER['SERVER_PORT'];
        }
        
        if (! isset($this->user) && isset($_SERVER['PHP_AUTH_USER'])) {
            $this->user = $_SERVER['PHP_AUTH_USER'];
        }
        
        if (! isset($this->pass) && isset($_SERVER['PHP_AUTH_PW'])) {
            $this->pass = $_SERVER['PHP_AUTH_PW'];
        }
        
        $this->_ext = $this->_defaultExt;
        
        if (array_key_exists('path', $this->_uri) && strpos($this->_uri['path'], '.') !== false) {
            $tmpArr = explode('.', $this->_uri['path']);
            if (count($tmpArr) > 0) {
                $this->_ext = strtolower(array_pop($tmpArr));
            }
        }
    }

    /**
     * Заполнить исходные массивы параметров согласно
     * полученным от пользователя данным
     *
     * @return void
     */
    protected function _prepareRequestData()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_cookie = $_COOKIE;
        
        /**
         * strip slashes if magic_quotes is enabled
         */
        if (get_magic_quotes_gpc()) {
            $this->_stripSlashesRecursive($this->_get);
            $this->_stripSlashesRecursive($this->_post);
            $this->_stripSlashesRecursive($this->_cookie);
        }
        
        $this->_params = array_merge($this->_get, $this->_post);
    }

    /**
     * Вспомогательная функция для рекурсивного удаления экранирования
     * элементов массива в случае включенной настройки magic_quotes
     *
     * @param array $var            
     * @return void
     */
    protected function _stripSlashesRecursive(array &$var)
    {
        if (! is_array($var)) {
            return;
        }
        foreach ($var as $k => $v) {
            if (is_string($var[$k])) {
                $var[$k] = stripslashes($v);
            } elseif (is_array($var[$k])) {
                $this->_stripSlashesRecursive($var[$k]);
            }
        }
    }

    /**
     * Обработка заголовков полученных от пользователя
     *
     * @return void
     */
    protected function _prepareHeaders()
    {
        if (function_exists('getallheaders')) {
            $this->_headers = getallheaders();
        } else {
            $this->_headers = $this->__getAllHeaders();
        }
    }

    /**
     * Вспомогательная функция для получения пришедших заголовков, в
     * случае, если веб-сервер не apache и не работает php-функция, характерная
     * только для apache
     *
     * @return array
     */
    private function __getAllHeaders()
    {
        $headers = array();
        
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Вернуть значение заголовока по ключу (имени заголовка)
     *
     * @param string $key            
     * @return string|null
     */
    public function getHeader($key)
    {
        return isset($this->_headers[$key]) ? $this->_headers[$key] : null;
    }

    /**
     * Вернуть значение хоста
     *
     * @return string|null
     */
    public function getHost()
    {
        return isset($this->host) ? $this->host : null;
    }

    /**
     * Вернуть часть url (/a/b/c/d)
     *
     * @return string
     */
    public function getPath()
    {
        return isset($this->path) ? $this->path : '';
    }

    /**
     * Вернуть часть url (?a=1&b=2)
     *
     * @return string
     */
    public function getQuery()
    {
        return isset($this->query) ? $this->query : '';
    }

    /**
     * Вернуть часть url (/a/b/c?d=x&e=y)
     *
     * @return string
     */
    public function getUri()
    {
        $uri = $this->path;
        if (strlen($this->query)) {
            $uri .= "?{$this->query}";
        }
        return $uri;
    }

    /**
     * Вернуть часть url (http://xyz.com/a/b/c)
     *
     * @return string
     */
    public function getUrl()
    {
        $uri = $this->getUri();
        if (! strlen($this->host)) {
            return $uri;
        }
        
        $host = $this->host;
        if ($this->port != 80) {
            $host .= ":{$this->port}";
        }
        
        if (strlen($this->user)) {
            $auth = $this->user;
            if (strlen($this->pass)) {
                $auth .= ":{$this->pass}";
            }
            $host = "$auth@$host";
        }
        $url = "{$this->scheme}://$host" . $uri;
        return $url;
    }

    /**
     * Вернуть массив пармметров url
     *
     * @return array
     */
    public function getUriArray()
    {
        return $this->_uri;
    }

    /**
     * Получить запрашиваемое расширение
     *
     * @return string
     */
    public function getExt()
    {
        return $this->_ext;
    }

    /**
     * Задать расширение "по умолчанию"
     *
     * @return void
     */
    public function setDefaultExt()
    {
        $this->_ext = $this->_defaultExt;
    }

    /**
     * Вернуть расширение "по умолчанию"
     *
     * @return string
     */
    public function getDefaultExt()
    {
        return $this->_defaultExt;
    }

    /**
     * Получить один отдельно взятый параметр GET
     *
     * @param int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function getGetParam($key, $filter = null, $default = null)
    {
        return $this->_getFromArray($this->_get, $key, $filter, $default);
    }

    /**
     * Получить массив параметров GET
     *
     * @return array
     */
    public function getGetParams()
    {
        return $this->_get;
    }

    /**
     * Получить один отдельно взятый параметр POST
     *
     * @param int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function getPostParam($key, $filter = null, $default = null)
    {
        return $this->_getFromArray($this->_post, $key, $filter, $default);
    }

    /**
     * Получить массив параметров POST
     *
     * @return array
     */
    public function getPostParams()
    {
        return $this->_post;
    }

    /**
     * Получить один отдельно взятый cookie
     *
     * @param int $key            
     * @param string $filter            
     * @param mixed $default            
     * @return mixed
     */
    public function getCookie($key, $filter = null, $default = null)
    {
        return $this->_getFromArray($this->_cookie, $key, $filter, $default);
    }

    /**
     * Получить массив Cookie
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->_cookie;
    }
}