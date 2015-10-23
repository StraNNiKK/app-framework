<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Response
 * @version    $Id:$
 */
require_once ('App/Server/Response.php');

/**
 * Класс ответа на http-запрос
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Response_Http extends App_Server_Response
{

    /**
     * Часть заголовка вида HTTP/1.1 200 OK
     *
     * @var string
     */
    protected $_code;

    /**
     * Часть заголовка вида HTTP/1.1 200 OK
     *
     * @var string
     */
    protected $_message;

    /**
     * Часть заголовка вида HTTP/1.1 200 OK
     *
     * @var string
     */
    protected $_httpVersion = '1.x';

    /**
     * Массив заголовков ответа
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * Массив кодов и соответсвующих им сообщений
     *
     * @static
     *
     * @var array
     */
    protected static $_messages = array(
        
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * Получить значение свойства объекта ответа
     *
     * @param string $name            
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'code':
                return $this->_code;
            case 'message':
                return $this->_message;
            case 'headers':
                return $this->_headers;
            case 'version':
                return $this->_httpVersion;
            default:
                return null;
        }
    }

    /**
     * Задать значение свойства объекта ответа
     *
     * @param string $name
     *            Имя свойства
     * @param string $value
     *            Значение свойства
     * @return string
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'code':
                return $this->setCode($value);
            case 'headers':
                return $this->setHeaders($value);
            case 'version':
                return $this->setVersion($value);
        }
    }

    /**
     * Проверить, объект готов для выдачи контента и заголовков пользователю
     * или же нет
     *
     * @return boolean
     */
    public function isReady()
    {
        return isset($this->_code) ? true : false;
    }

    /**
     * Преобразование к строке
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Преобразование к строке
     *
     * @return string
     */
    public function toString()
    {
        $this->getReady();
        
        if (headers_sent()) {
            trigger_error('Headers Already Sent', E_USER_WARNING);
        } else {
            $this->_sendHeaders();
        }
        
        return parent::toString();
    }

    /**
     * Отправить заголовки пользователю
     *
     * @return void
     */
    public function _sendHeaders()
    {
        header($this->getStatusLine());
        
        foreach ($this->headers as $name => $value) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            if (! is_array($value)) {
                header("{$name}: {$value}");
            } else {
                for ($i = 0, $cnt = count($value); $i < $cnt; $i ++) {
                    $subvalue = array_shift($value);
                    header("$name: $subvalue", ! (boolean) $i);
                }
            }
        }
    }

    /**
     * Задать код заголовка ответа
     *
     * @param string $code            
     * @return void
     */
    public function setCode($code)
    {
        $code = intval($code);
        if (! isset(self::$_messages[$code])) {
            return false;
        }
        $this->_code = $code;
        $this->_message = self::$_messages[$code];
    }

    /**
     * Задать заголовоки ответа (в виде массива)
     *
     * @param array $headers            
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Задать версию заголовка ответа
     *
     * @param string $version            
     * @return void
     */
    public function setVersion($version)
    {
        if (preg_match('/^1\.[x\d]$/', $version)) {
            $this->_httpVersion = $version;
        }
    }

    /**
     * Задать заголовок ответа
     *
     * @param string $name            
     * @param string $value            
     */
    public function setHeader($name, $value)
    {
        $name = strtolower($name);
        $this->_headers[$name] = $value;
    }

    /**
     * Получить все заголовки
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Получить один заголовок по имени
     *
     * @param string $name            
     * @return string
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }
        return '';
    }

    /**
     * Получить HTTP заголовок с кодом ответа, версией протокола и сообщением
     *
     * @return string
     */
    public function getStatusLine()
    {
        return "HTTP/{$this->_httpVersion} {$this->_code} {$this->_message}";
    }

    /**
     * Получить все заголовки в виде строки
     *
     * @param boolean $statusLine
     *            Включать или нет заголовок вида (IE "HTTP 200 OK")
     * @param string $br
     *            Разделитель заголовков (например "\n", "\r\n", "<br />")
     * @return string
     */
    public function getHeadersAsString($statusLine = true, $br = "\n")
    {
        $str = '';
        
        if ($statusLine) {
            $str = $this->getStatusLine() . $br;
        }
        
        // Iterate over the headers and stringify them
        foreach ($this->_headers as $name => $value) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            if (! is_array($value)) {
                $str .= "{$name}: {$value}{$br}";
            } else {
                foreach ($value as $subval) {
                    $str .= "{$name}: {$subval}{$br}";
                }
            }
        }
        
        return $str;
    }

    /**
     * Проверить, был ли произведен редирект
     *
     * @return boolean
     */
    public function isRedirect()
    {
        $restype = floor($this->_code / 100);
        if ($restype == 3) {
            return true;
        }
        
        return false;
    }

    /**
     * Произвести редирект
     *
     * @param string $location            
     * @param boolean $permanent            
     * @return void
     */
    public function redirect($location, $permanent = false)
    {
        $this->setHeader('Location', $location);
        $this->setCode($permanent ? 301 : 302);
    }

    /**
     * Если объект не готов для выдачи контента, заголовков и тп.,
     * то сделать его таковым
     *
     * @return void
     */
    public function getReady()
    {
        if (! $this->isReady()) {
            $this->setCode(200, true);
        }
    }

    /**
     * В качестве заголовков задать ошибки,
     * сгенерированные во время работы приложения
     *
     * @return void
     */
    public function reportErrors()
    {
        $errors = App_Server_Error_Handler::getErrors();
        
        foreach ($errors as $type => $errs) {
            $this->setHeader('Php-report-' . $type, implode("\r\n", $errs));
        }
    }
}