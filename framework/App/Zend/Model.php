<?php

class App_Zend_Model extends Zend_Db_Table_Abstract
{

    protected static $_dbAdapters = array();

    protected static $_dbProfiler = null;

    public function __construct($dbConnectionName = null)
    {
        $db = self::getModelAdapter($dbConnectionName);
        
        parent::__construct(array(
            'db' => $db
        ));
    }

    static public function getModelAdapter($dbConnectionName = null)
    {
        $config = App_Server::getConfig();
        
        if (! array_key_exists('db', $config) || ! is_array($config['db']) || count($config['db']) == 0) {
            throw new Exception('Incorrect database config');
        }
        
        $dbConfig = $config['db'];
        
        if ($dbConnectionName && array_key_exists(strval($dbConnectionName), $dbConfig)) {
            $connectionConfig = $dbConfig[strval($dbConnectionName)];
        } else {
            $dbConnectionName = key($dbConfig);
            $connectionConfig = array_shift($dbConfig);
        }
        
        if (array_key_exists($dbConnectionName, self::$_dbAdapters)) {
            $db = self::$_dbAdapters[$dbConnectionName];
        } else {
            $db = Zend_Db::factory($connectionConfig['adapter'], $connectionConfig);
            
            if (! self::$_dbProfiler) {
                self::$_dbProfiler = new Zend_Db_Profiler();
                self::$_dbProfiler->setEnabled(true);
            }
            
            $db->setProfiler(self::$_dbProfiler);
            
            self::$_dbAdapters[$dbConnectionName] = $db;
        }
        
        return $db;
    }

    static public function getProfiler()
    {
        return self::$_dbProfiler;
    }
}