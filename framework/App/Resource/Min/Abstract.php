<?php

abstract class App_Resource_Min_Abstract
{

    protected function _saveFile($content, $path)
    {
        $fp = fopen($path, 'w');
        fwrite($fp, $content);
        fclose($fp);
        unset($content);
    }
}