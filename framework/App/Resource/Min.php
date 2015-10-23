<?php

class App_Resource_Min
{

    protected $_config = array();

    protected $_minificator = array();

    public function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        $this->_config = $config['resourses'];
    }

    public function minFiles(array $files, $outFile, $type)
    {
        $minificator = $this->initMinificator($type);
        
        if ($minificator) {
            $minificator->minFiles($files, $outFile);
            if ($this->checkAutoRemoveOldFiles($type)) {
                $this->_removeOldFiles($outFile);
            }
            return true;
        } else {
            return false;
        }
    }

    public function minContent($str, $outFile, $type)
    {
        $minificator = $this->initMinificator($type);
        
        if ($minificator) {
            $minificator->minContent($str, $outFile);
            return true;
        } else {
            return false;
        }
    }

    protected function _removeOldFiles($outFile)
    {
        $parts = explode('/', $outFile);
        $fileName = array_pop($parts);
        $fileParts = explode('.', $fileName);
        $newFilePath = implode('/', $parts) . '/';
        
        for ($i = 0; $i < count($fileParts) - 2; $i ++) {
            $newFilePath .= $fileParts[$i] . '.';
        }
        
        $newFilePath .= '*';
        
        $foundFilesArr = glob($newFilePath);
        
        if (count($foundFilesArr) > 0) {
            foreach ($foundFilesArr as $filePath) {
                if ($outFile != $filePath) {
                    unlink($filePath);
                }
            }
        }
    }

    public function getMinClass($type)
    {
        if (array_key_exists($type, $this->_config) && array_key_exists('min', $this->_config[$type]) && trim($this->_config[$type]['min']) != '') {
            return $this->_config[$type]['min'];
        } else {
            return null;
        }
    }

    public function initMinificator($type)
    {
        $minificator = $this->getMinificator($type);
        
        if (! $minificator) {
            $minificatorClass = $this->getMinClass($type);
            
            if ($minificatorClass) {
                $res = App_Loader::load($minificatorClass);
                if ($res) {
                    $this->_minificator[$type] = new $minificatorClass();
                    return $this->_minificator[$type];
                }
            } else {
                $this->_minificator[$type] = new App_Resource_Min_None();
            }
        } else {
            return $minificator;
        }
    }

    public function checkMinificator($type)
    {
        return (array_key_exists($type, $this->_minificator) && $this->_minificator[$type]) ? true : false;
    }

    public function getMinificator($type)
    {
        if (array_key_exists($type, $this->_minificator)) {
            return $this->_minificator[$type];
        } else {
            return null;
        }
    }

    public function checkAutoRemoveOldFiles($type)
    {
        if (array_key_exists($type, $this->_config) && array_key_exists('autoRemoveOldFiles', $this->_config[$type]) && $this->_config[$type]['autoRemoveOldFiles'] == true) {
            return true;
        }
        
        return false;
    }
}