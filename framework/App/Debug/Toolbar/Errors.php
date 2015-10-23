<?php

class App_Debug_Toolbar_Errors implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    public function __construct()
    {
        $errors = App_Server_Error_Handler::getErrors();
        
        $html = '';
        $short = array();
        
        if (count($errors) > 0) {
            foreach ($errors as $type => $errs) {
                $html .= '<h5>' . ucfirst($type) . ':</h5>';
                $html .= implode('<br />', $errs) . '<br />';
                $short[] = ucfirst($type) . ': ' . count($errs);
            }
        } else {
            $html .= 'Cool! no errors :)';
        }
        
        $this->_html = '<h4>Errors</h4>' . $html;
        $this->_short = 'Errors: ' . (count($short) > 0 ? implode(', ', $short) : 0);
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