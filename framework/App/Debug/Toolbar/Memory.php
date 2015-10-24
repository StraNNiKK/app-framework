<?php

class App_Debug_Toolbar_Memory implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    public function __construct()
    {
        $memoryMessage = '';
        
        if (function_exists('memory_get_peak_usage')) {
            $memoryLimit = intval(ini_get('memory_limit'));
            $memoryMessage = round(memory_get_peak_usage() / 1024) . 'K' . (($memoryLimit > 0) ? (' of ' . $memoryLimit) : '');
        } else {
            $memoryMessage = 'MemUsage n.a.';
        }
        
        $html = '<h4>Memory Usage</h4>';
        $html .= $memoryMessage;
        
        $this->_html = $html;
        $this->_short = $memoryMessage;
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