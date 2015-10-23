<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Stream
 * @version    $Id:$
 */
require_once 'App/Server.php';
require_once 'App/Server/Stack.php';

/**
 * Реализация url-обретки для запросов вида (internal://...)
 *
 * @category App
 * @package App_Server
 * @subpackage Stream
 */
class App_Server_Stream_Wrapper
{

    /**
     * Позиция указателя в считываемом потоке
     *
     * @var int
     */
    protected $_position = 0;

    /**
     * Прочитанный из потока контент
     *
     * @var string
     */
    protected $_content = '';

    /**
     * Предел редиректа
     *
     * @var int
     */
    protected $_redirectLimit = 2;

    /**
     * Объект запроса
     *
     * @var App_Server_Request
     */
    protected $_request;

    /**
     * Флаг для внутреннего использования
     *
     * @var boolean
     */
    protected $_isHandled = false;

    /**
     * Метод, вызываемый сразу после создания объекта
     *
     * @param string $path            
     * @param string $mode            
     * @param int $options            
     * @param string $openedPath            
     *
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$openedPath)
    {
        require_once 'App/Server/Request/Internal.php';
        $this->_request = new App_Server_Request_Internal($path);
        
        return true;
    }

    /**
     * Метод чтения данных из потока
     *
     * @param int $count
     *            Количество прочитанных байт
     * @return string
     */
    public function stream_read($count)
    {
        $this->_handle();
        
        $ret = substr($this->_content, $this->_position, $count);
        $this->_position += strlen($ret);
        
        return $ret;
    }

    /**
     * Метод записи данных в поток
     *
     * @param array $data
     *            Массив, который мы хотим передать
     * @return boolean
     */
    public function stream_write($data)
    {
        if ($this->_isHandled) {
            return false;
        }
        
        return $this->_request->setRawPost(unserialize($data));
    }

    /**
     * Вернуть текущую позиция указателя
     * в читаемом потоке
     *
     * @return int
     */
    public function stream_tell()
    {
        return $this->_position;
    }

    /**
     * Тест конца потока
     *
     * @return boolean
     */
    public function stream_eof()
    {
        $this->_handle();
        return $this->_position >= strlen($this->_content);
    }

    /**
     * Возвращает информацию о текущем положении указателя
     * и использовании потока
     *
     * @return array
     */
    public function stream_stat()
    {
        $this->_handle();
        
        return array(
            'size' => strlen($this->_content),
            'atime' => null,
            'mtime' => null,
            'ctime' => null
        );
    }

    /**
     * Помещает положение указателя в определенное место на определенный отступ
     *
     * @param int $offset            
     * @param int $whence            
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        $this->_handle();
        
        switch ($whence) {
            case SEEK_SET:
                $newPosition = $offset;
                break;
            case SEEK_CUR:
                $newPosition += $offset;
                break;
            case SEEK_END:
                $newPosition = strlen($this->_content) + $offset;
                break;
            default:
                return false;
        }
        
        if ($newPosition < 0 || $newPosition > strlen($this->_content)) {
            return false;
        }
        
        $this->_position = $newPosition;
        
        return true;
    }

    /**
     * Создать объект internal запроса, а также соотвествующий ему
     * объект App_Server, поместить последний в стек и начать процесс
     * диспетчеризации
     *
     * @return void
     */
    public function _handle()
    {
        if ($this->_isHandled) {
            return;
        }
        
        $server = App_Server::newInstance();
        $response = $server->run($this->_request);
        
        while ($response->isRedirect() && $this->_redirectLimit --) {
            $this->_request = new App_Server_Request_Internal($response->getHeader('Location'));
            $response = $server->run($this->_request);
        }
        
        $this->_content = $response->getBody();
        
        App_Server_Stack::pop();
        $server = null;
        
        $this->_isHandled = true;
        $this->_position = 0;
    }
}