<?php

class App_Resource_Store
{

    protected $_config = array();

    protected $_store = array();

    public function __construct()
    {
        $config = App_Server::getInstance()->getConfig();
        foreach ($config['resourses'] as $key => $val) {
            if (array_key_exists('store', $val) && (bool) $val['store'] == true) {
                $resStoreClassName = $val['store'];
                $res = App_Loader::load($resStoreClassName);
                if ($res) {
                    $this->_store[$key] = new $resStoreClassName();
                    $this->_store[$key]->init();
                }
            }
        }
    }

    public function getStore($resType)
    {
        if (array_key_exists($resType, $this->_store)) {
            return $this->_store[$resType];
        } else {
            return null;
        }
    }

    public function set($resType, $key, $value)
    {
        $store = $this->getStore($resType);
        if ($store) {
            $store->set($key, $value);
        }
    }

    public function get($resType, $key)
    {
        $store = $this->getStore($resType);
        if ($store) {
            return $store->get($key);
        } else {
            return false;
        }
    }

    public function check($resType, $key)
    {
        $store = $this->getStore($resType);
        if ($store) {
            return $store->check($key);
        } else {
            return false;
        }
    }
}