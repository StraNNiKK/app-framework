<?php

class App_Resource_Store_Memory implements App_Resource_Store_Interface
{

    protected $_store = null;

    public function init()
    {
        $this->_store = App_Store_Memory::getInstance();
    }

    public function set($key, $value)
    {
        $this->_store->set($key, $value);
    }

    public function get($key)
    {
        $val = $this->_store->get($key);
        
        return $val ? $val : false;
    }
}