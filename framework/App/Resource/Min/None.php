<?php

class App_Resource_Min_None extends App_Resource_Min_Abstract implements App_Resource_Min_Interface
{

    public function minFiles($files, $outFile)
    {
        $content = '';
        
        foreach ($files as $file) {
            if (file_exists($file['file'])) {
                $content .= file_get_contents($file['file']);
            }
        }
        
        $this->_saveFile($content, $outFile);
    }

    public function minContent($content, $outFile)
    {
        $this->_saveFile($content, $outFile);
    }
}