<?php

class App_Debug_Toolbar_Files implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    public function __construct()
    {
        $included = get_included_files();
        sort($included);
        
        $html = '<h4>File Information</h4>';
        $html .= count($included) . ' Files Included<br />';
        $size = 0;
        
        $files = '';
        
        foreach ($included as $file) {
            $size += filesize($file);
            $files .= $file . '<br />';
        }
        $html .= 'Total Size: ' . round($size / 1024, 1) . 'K<br />';
        $html .= 'Files:<br />';
        $html .= $files;
        
        $this->_html = $html;
        $this->_short = count($included) . ' files';
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