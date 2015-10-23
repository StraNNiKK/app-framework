<?php

class App_Resource_Builder
{

    protected $_store = null;

    protected $_minificator = null;

    protected $_fullPath2res = '';

    protected $_config = array();

    public function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        $this->_config = $config['resourses'];
        
        $this->_store = new App_Resource_Store();
        $this->_minificator = new App_Resource_Min();
        $this->_fullPath2res = $this->_config['common']['public'];
    }

    public function createAndGetContent($type, $filePath, $res)
    {
        $content = false;
        
        if ($res && array_key_exists('group', $res)) {
            $this->_initMinificator($type);
            
            if ($res['group'] == true) {
                $this->_createAndGetContentGroup($filePath, $res['params'], $type);
            } else {
                $this->_createAndGetContentResource($filePath, $res, $type);
            }
        }
    }

    private function __groupFiles($type, $resFiles)
    {
        $arrGroup = array(
            'groups' => array(),
            'notGroup' => array()
        );
        
        if (count($resFiles[$type]) > 0) {
            foreach ($resFiles[$type]['items'] as $item) {
                if (array_key_exists('group', $item) && $item['group'] != '') {
                    $groupName = $item['group'];
                    unset($item['group']);
                    if (! array_key_exists($groupName, $arrGroup['groups'])) {
                        $arrGroup['groups'][$groupName] = array();
                    }
                    $arrGroup['groups'][$groupName][] = $item;
                } else {
                    $arrGroup['notGroup'][] = $item;
                }
            }
        }
        
        return $arrGroup;
    }

    private function __filemtimeErrorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_WARNING:
                return true;
                break;
        }
    }

    private function __getVersion($arr, $type)
    {
        if (array_key_exists('version', $arr) && (bool) $arr['version'] != false) {
            return $arr['version'];
        } else {
            $version = $this->_store->get($type, $arr['filepath']);
            if ($version !== false) {
                return $version;
            } else {
                // ob_start();
                set_error_handler(array(
                    $this,
                    '__filemtimeErrorHandler'
                ));
                $time = @filemtime($this->_fullPath2res . $arr['filepath']);
                restore_error_handler();
                // ob_end_clean();
                
                if ($time) {
                    $this->_store->set($type, $arr['filepath'], $time);
                    return $time;
                } else {
                    return 0;
                }
            }
        }
    }

    private function __hash($string)
    {
        return abs(crc32($string));
    }

    private function __createHash($arr, $type, $isGroup = false)
    {
        $str = '';
        
        $minClass = $this->_minificator->getMinClass($type);
        
        if ($isGroup) {
            foreach ($arr as $k => $v) {
                $str .= $v['filepath'] . $this->__getVersion($v, $type);
                $str .= (array_key_exists('min', $v) && $v['min'] == true) ? 1 : 0;
            }
            $str .= $minClass;
        } else {
            $str = $arr['filepath'] . $this->__getVersion($arr, $type) . $minClass;
        }
        
        return $this->__hash($str);
    }

    private function __createName($name, $hash, $resType = 'file', $filepath = '')
    {
        if ($resType == 'file') {
            $name = 'f.' . $name . '.' . $this->__hash($filepath) . '.' . $hash;
        } else 
            if ($resType == 'group') {
                $name = 'g.' . $name . '.' . $hash;
            } else 
                if ($resType == 'raw') {
                    $request = App_Server::getInstance()->getRequest();
                    $name = 'r.' . $name . '.' . $this->__hash($request->getUri()) . '.' . $hash;
                }
        
        return $name;
    }

    private function __getRealName($filePath)
    {
        $urlArr = parse_url($filePath);
        $filePathArr = explode('/', $urlArr['path']);
        $fileName = end($filePathArr);
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));
        
        return $fileName;
    }

    public function prepareResources($type, $resFiles)
    {
        if (count($resFiles[$type]) > 0) {
            $arrGroup = $this->__groupFiles($type, $resFiles);
            
            // Proccess resources.
            
            $res = array();
            
            foreach ($arrGroup['groups'] as $name => $group) {
                $res[] = $this->_proccessGroup($name, $group, $type);
            }
            
            foreach ($arrGroup['notGroup'] as $item) {
                $res[] = $this->_proccessResource($item, $type);
            }
            
            return $res;
        }
    }

    public function prepareRawResources($type, $rawData)
    {
        if (count($rawData[$type]) > 0) {
            $this->_initMinificator($type);
            $minificatorType = $this->_minificator->getMinClass($type);
            $content = implode("\r\n", $rawData[$type]);
            
            $hash = $this->__hash($content . $minificatorType);
            $filename = $this->__createName('raw', $hash, 'raw');
            // $filename = 'raw.' . $this->__hash($content . $minificatorType);
            $path2file = $this->_config[$type]['cache'] . $filename . '.' . $type;
            
            $version = $this->__getVersion(array(
                'filepath' => $path2file
            ), $type);
            
            if ($version == 0) {
                try {
                    $fullPath2file = $this->_fullPath2res . $path2file;
                    $this->_minificator->minContent($content, $fullPath2file, $type);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
            
            return $path2file;
        }
        
        return false;
    }

    public function prepareHtmlResources($type, $htmlData)
    {
        if (count($htmlData[$type]) > 0) {
            $content = implode("\r\n", $htmlData[$type]);
        } else {
            return false;
        }
        
        if ($type == 'js') {
            return '<script type="text/javascript">' . $content . "</script>\n";
        } elseif ($type == 'css') {
            return '<style type="text/css">' . $content . "</style>\n";
        }
    }

    protected function _proccessResource($arr, $type)
    {
        $file = '';
        
        if ($this->_config[$type]['min'] && array_key_exists('min', $arr) && $arr['min'] == true) {
            $fileName = $this->__getRealName($arr['filepath']);
            $hash = $this->__createHash($arr, $type);
            $file = $this->_config[$type]['cache'] . $this->__createName($fileName, $hash, 'file', $arr['filepath']) . '.' . $type;
            // $file = $this->_config[$type]['cache'] . $fileName . '.' . $this->__createHash($arr, $type) . '.' . $type;
            
            if (! file_exists($this->_fullPath2res . $file)) {
                $this->createAndGetContent($type, $file, array(
                    'group' => false,
                    'min' => true,
                    'filepath' => $arr['filepath']
                ));
            }
        } else {
            $urlPartsArr = parse_url($arr['filepath']);
            
            if (! array_key_exists('query', $urlPartsArr)) {
                $file = $arr['filepath'] . '?v=' . $this->__getVersion($arr, $type);
            } else {
                $file = $arr['filepath'];
            }
        }
        
        return $file;
    }

    protected function _proccessGroup($groupName, $arr, $type)
    {
        $params = array();
        
        foreach ($arr as $k => $v) {
            $params[] = array(
                'min' => (array_key_exists('min', $v) && $this->_config[$type]['min']) ? $v['min'] : false,
                'filepath' => $v['filepath']
            );
        }
        
        $hash = $this->__createHash($arr, $type, true);
        $file = $this->_config[$type]['cache'] . $this->__createName($groupName, $hash, 'group') . '.' . $type;
        // $file = $this->_config[$type]['cache'] . $groupName . '.' . $this->__createHash($arr, $type, true) . '.' . $type;
        
        if (! file_exists($this->_fullPath2res . $file)) {
            $this->createAndGetContent($type, $file, array(
                'group' => true,
                'params' => $params
            ));
        }
        
        return $file;
    }

    public function decorator(&$arr, $type)
    {
        if (is_array($arr) && count($arr) > 0) {
            foreach ($arr as &$item) {
                $this->__htmlDecorate($item, $type);
            }
        } else 
            if (is_string($arr)) {
                $this->__htmlDecorate($arr, $type);
            }
    }

    private function __htmlDecorate(&$item, $type)
    {
        if ($type == 'js') {
            $item = "<script type=\"text/javascript\" src=\"" . $item . "\"></script>\n";
        } else 
            if ($type == 'css') {
                $item = "<link href=\"" . $item . "\" rel=\"stylesheet\" type=\"text/css\">\n";
            }
    }

    protected function _createAndGetContentResource($filePath, $params, $type)
    {
        $originalFilepath = $this->_fullPath2res . $params['filepath'];
        $fullPath2cachedFile = $this->_fullPath2res . $filePath;
        
        $minArr = array(
            0 => array(
                'min' => true,
                'file' => $originalFilepath
            )
        );
        
        try {
            return $this->_minificator->minFiles($minArr, $fullPath2cachedFile, $type);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function _createAndGetContentGroup($filePath, $groupFiles, $type)
    {
        $fullPath2cachedFile = $this->_fullPath2res . $filePath;
        
        $content = false;
        
        if (count($groupFiles) > 0) {
            $minArr = array();
            foreach ($groupFiles as $file) {
                $minArr[] = array(
                    'min' => (bool) $file['min'],
                    'file' => $this->_fullPath2res . $file['filepath']
                );
            }
            
            try {
                return $this->_minificator->minFiles($minArr, $fullPath2cachedFile, $type);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        
        return $content;
    }

    protected function _initMinificator($type)
    {
        $this->_minificator->initMinificator($type);
    }
}