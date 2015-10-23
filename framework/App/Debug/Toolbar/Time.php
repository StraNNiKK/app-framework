<?php

class App_Debug_Toolbar_Time implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    public function __construct()
    {
        $time = ((microtime(true) - $_SERVER['REQUEST_TIME']) * 1000) . 'ms';
        
        $html = '<h4>Time Information (include web-server + php script working time)</h4>';
        $html .= $time . '<br />';
        
        $this->_short = $time;
        $this->_html = $html;
    }

    public function getHtml()
    {
        return $this->_html;
    }

    public function getShortName()
    {
        return $this->_short;
    }

    public function getIcon()
    {
        return false;
    }
}