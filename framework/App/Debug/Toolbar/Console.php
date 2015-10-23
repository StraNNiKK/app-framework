<?php

class App_Debug_Toolbar_Console implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    public function __construct()
    {
        $logs = App_Debug::getLog();
        
        $html = '';
        
        if (count($logs) > 0) {
            foreach ($logs as $str) {
                $html .= $str . '<br />';
            }
        } else {
            $html .= 'Please, use global function <i>console(mixed)</i> to output some data in this bar';
        }
        
        $this->_html = '<h4>Console</h4>' . $html;
        $this->_short = 'Console';
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