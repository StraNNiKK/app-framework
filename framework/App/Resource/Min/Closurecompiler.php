<?php

class App_Resource_Min_Closurecompiler extends App_Resource_Min_Abstract implements App_Resource_Min_Interface
{

    protected $_useCompilationLevel;

    protected $_compilationLevel = array(
        0 => 'WHITESPACE_ONLY',
        1 => 'SIMPLE_OPTIMIZATIONS',
        2 => 'ADVANCED_OPTIMIZATIONS'
    );

    protected $_path2compiler;

    public function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        
        $this->_path2compiler = $config['resourses']['js']['min']['path2closurecompiler'];
        $this->_useCompilationLevel = 1;
    }

    protected function _getCurrentCompilationLevel()
    {
        return $this->_compilationLevel[$this->_useCompilationLevel];
    }

    public function minFiles($files, $outFile)
    {
        $content = '';
        
        $minAllFiles = true;
        $newFilesArray = array();
        
        foreach ($files as $file) {
            if (file_exists($file['file'])) {
                $newFilesArray[] = $file;
                if (((bool) $file['min']) == false) {
                    $minAllFiles = false;
                }
            }
        }
        
        // if all files should be minified run speсial console command
        if ($minAllFiles) {
            $command = 'java -jar ' . $this->_path2compiler;
            foreach ($newFilesArray as $f) {
                $command .= ' --js ' . $f['file'];
            }
            $command .= ' --js_output_file ' . $outFile;
            $command .= ' --compilation_level ' . $this->_getCurrentCompilationLevel();
            
            shell_exec($command);
            // if there are some files that shouldn't be minified
        } else {
            foreach ($newFilesArray as $f) {
                $c = '';
                if ((bool) $f['min']) {
                    $c = $this->_runMinificator($f['file']);
                } else {
                    $c = file_get_contents($f['file']);
                }
                $content .= $c;
            }
            
            $this->_saveFile($content, $outFile);
        }
    }

    public function minContent($content, $outFile)
    {
        $this->_saveFile($content, $outFile);
        $modifiedContent = $this->_runMinificator($outFile);
        $this->_saveFile($modifiedContent, $outFile);
    }

    protected function _runMinificator($filename)
    {
        return shell_exec('java -jar ' . $this->_path2compiler . ' --js ' . $filename . ' --compilation_level ' . $this->_getCurrentCompilationLevel());
    }
}