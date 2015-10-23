<?php

class App_Resource
{

    protected static $_instance;

    protected $_builderObj = null;

    protected $_config = array();

    protected $_resFiles = array(
        'js' => array(
            'names' => array(),
            'items' => array()
        ),
        'css' => array(
            'names' => array(),
            'items' => array()
        )
    );

    protected $_rawData = array(
        'js' => array(),
        'css' => array()
    );

    protected $_htmlData = array(
        'js' => array(),
        'css' => array()
    );

    private function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        $this->_config = $config['resourses'];
        
        $this->_builderObj = new App_Resource_Builder();
    }

    private function __clone()
    {}

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * $path,
     * $config = array(
     * 'version' => string | int | true | false
     * 'min' => true | false
     * 'group' => 'name'
     * )
     */
    public function prependJs($filename, array $config = array())
    {
        $this->_prepend($filename, $config, 'js');
        return $this;
    }

    public function appendJs($filename, array $config = array())
    {
        $this->_append($filename, $config, 'js');
        return $this;
    }

    public function prependCss($filename, array $config = array())
    {
        $this->_prepend($filename, $config, 'css');
        return $this;
    }

    public function appendCss($filename, array $config = array())
    {
        $this->_append($filename, $config, 'css');
        return $this;
    }

    public function prependRawJs($text)
    {
        $this->_prependRaw($text, 'js');
        return $this;
    }

    public function appendRawJs($text)
    {
        $this->_appendRaw($text, 'js');
        return $this;
    }

    public function prependRawCss($text)
    {
        $this->_appendRaw($text, 'css');
        return $this;
    }

    public function appendRawCss($text)
    {
        $this->_appendRaw($text, 'css');
        return $this;
    }

    public function prependInlineRawJs($text)
    {
        $this->_prependInlineRaw($text, 'js');
        return $this;
    }

    public function appendInlineRawJs($text)
    {
        $this->_appendInlineRaw($text, 'js');
        return $this;
    }

    public function prependInlineRawCss($text)
    {
        $this->_appendInlineRaw($text, 'css');
        return $this;
    }

    public function appendInlineRawCss($text)
    {
        $this->_appendInlineRaw($text, 'css');
        return $this;
    }

    protected function _prepend($filename, array $config, $type)
    {
        if (! in_array($filename, $this->_resFiles[$type]['names'])) {
            $config['filepath'] = $filename;
            $this->_resFiles[$type]['names'][] = $filename;
            
            if (array_key_exists('autoMin', $this->_config[$type]) && $this->_config[$type]['autoMin'] == true) {
                $config['min'] = true;
            }
            
            array_unshift($this->_resFiles[$type]['items'], $config);
        }
    }

    protected function _append($filename, array $config, $type)
    {
        if (! in_array($filename, $this->_resFiles[$type]['names'])) {
            $config['filepath'] = $filename;
            $this->_resFiles[$type]['names'][] = $filename;
            
            if (array_key_exists('autoMin', $this->_config[$type]) && $this->_config[$type]['autoMin'] == true) {
                $config['min'] = true;
            }
            
            array_push($this->_resFiles[$type]['items'], $config);
        }
    }

    protected function _prependRaw($text, $type)
    {
        array_unshift($this->_rawData[$type], $text);
    }

    protected function _appendRaw($text, $type)
    {
        array_push($this->_rawData[$type], $text);
    }

    protected function _prependInlineRaw($text, $type)
    {
        array_unshift($this->_htmlData[$type], $text);
    }

    protected function _appendInlineRaw($text, $type)
    {
        array_push($this->_htmlData[$type], $text);
    }

    public function out($type)
    {
        if (! array_key_exists($type, $this->_resFiles)) {
            throw new Exception('Try to output unknown resource type');
        } else {
            $allResStr = '';
            $arr = $this->_builderObj->prepareResources($type, $this->_resFiles);
            
            if (count($arr) > 0) {
                $this->_builderObj->decorator($arr, $type);
                
                $allResStr .= implode('', $arr);
            }
            
            $str = $this->_builderObj->prepareRawResources($type, $this->_rawData);
            
            if ($str) {
                $this->_builderObj->decorator($str, $type);
                $allResStr .= $str;
            }
            
            $str = $this->_builderObj->prepareHtmlResources($type, $this->_htmlData);
            
            if ($str) {
                $allResStr .= $str;
            }
            
            return $allResStr;
        }
    }
}