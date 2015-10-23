<?php

class App_Factory
{

    private static $_createdObjects = array(
        'store',
        'config',
        'model'
    );

    private static $_storeTypes = array(
        'Local' => array(
            'allowNamespaces' => true
        ),
        'Session' => array(
            'allowNamespaces' => true
        ),
        'Memory' => array(
            'allowNamespaces' => false
        ),
        'Memcache' => array(
            'allowNamespaces' => false
        )
    );

    protected static $_models = array();

    protected static $_aliases = array();

    static public function addAlias($type, $params, $alias)
    {
        if (in_array($type, self::$_createdObjects)) {
            self::$_aliases[$alias] = array(
                $type,
                $params
            );
        } else {
            throw new Exception('Exception in App_Factory::addAlias() - Unknown store name');
        }
    }

    static public function getByAlias($alias)
    {
        if (array_key_exists($alias, self::$_aliases)) {
            
            $method = 'get' . ucfirst(self::$_aliases[$alias][0]);
            $params = self::$_aliases[$alias][1];
            
            if (count($params) > 0) {
                return call_user_func_array(array(
                    __CLASS__,
                    $method
                ), $params);
            } else {
                return call_user_func_array(array(
                    __CLASS__,
                    $method
                ));
            }
        } else {
            throw new Exception('Exception in App_Factory::getByAlias() - Unknown alias');
        }
    }

    static public function getStore($storeName, $namespace = null)
    {
        $storeName = ucfirst($storeName);
        
        if (array_key_exists($storeName, self::$_storeTypes)) {
            $storeClass = 'App_Store_' . $storeName;
            if (! class_exists($storeClass)) {
                throw new Exception("Exception in App_Factory::getStore() - Can't load store class {$storeClass}");
            }
            
            $val = self::$_storeTypes[$storeName];
            $namespace = self::$_storeTypes[$storeName]['allowNamespaces'] == true ? $namespace : null;
            
            if ($namespace) {
                return call_user_func(array(
                    $storeClass,
                    'getInstance'
                ), $namespace);
            } else {
                return call_user_func(array(
                    $storeClass,
                    'getInstance'
                ));
            }
        } else {
            throw new Exception('Exception in App_Factory::getStore() - Unknown store name');
        }
    }

    static public function getModel($name)
    {
        $className = ucfirst($name);
        
        if (! class_exists($className)) {
            throw new Exception("Exception in App_Factory::getModel() - Can't load model class {$className}");
        }
        
        if (! array_key_exists($className, self::$_models)) {
            self::$_models[$className] = new $className();
        }
        
        return self::$_models[$className];
    }

    static public function getConfig($configPath)
    {
        return App_Config::loadConfig($configPath);
    }
}