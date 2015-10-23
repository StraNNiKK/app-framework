<?php

class App_Debug_Toolbar_Database implements App_Debug_Toolbar_Interface
{

    protected $_short;

    protected $_html;

    protected $_timeFormat = '%.8f';

    public function __construct()
    {
        $this->_html = '<h4>Zend Db Profiler</h4><br />';
        $this->_short = 'Database: ';
        
        if (class_exists('App_Zend_Model')) {
            $profiler = App_Zend_Model::getProfiler();
            if ($profiler) {
                
                $num = 0;
                
                $totalNumQueries = 0;
                $totalElapsedSecs = 0;
                
                $queryProfiler = $profiler->getQueryProfiles();
                
                if ($queryProfiler && count($profiler->getQueryProfiles()) > 0) {
                    $num = 1;
                    
                    $totalNumQueries = $profiler->getTotalNumQueries();
                    $totalElapsedSecs = $profiler->getTotalElapsedSecs();
                    
                    foreach ($queryProfiler as $query) {
                        $this->_html .= '<strong>' . $num . '.</strong> ' . $query->getQuery() . '<br />' . '<strong>' . $num . '.</strong> ' . 'Time: ' . sprintf($this->_timeFormat, $query->getElapsedSecs()) . ' sec<br />';
                        
                        $num ++;
                    }
                }
                
                $this->_short .= $totalNumQueries . ' queries in ' . sprintf($this->_timeFormat, $totalElapsedSecs) . ' sec';
                
                return;
            }
        }
        
        $this->_short .= 'none';
        $this->_html .= 'no connections';
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