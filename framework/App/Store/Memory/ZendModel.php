<?php

class App_Store_Memory_ZendModel extends App_Zend_Model
{

    protected $_name;

    public function __construct($name)
    {
        $this->_name = $name;
        
        parent::__construct();
    }

    public function clear()
    {
        $this->delete('expire <= unix_timestamp()');
    }

    public function get($key, $getArray = false)
    {
        $select = $this->getAdapter()
            ->select()
            ->from($this->_name)
            ->where('`key` = ?', $key)
            ->where('expire > unix_timestamp()');
        
        $stmt = $this->getAdapter()
            ->query($select)
            ->fetch();
        
        if ($stmt) {
            if ($getArray) {
                return $stmt;
            } else {
                return $stmt['value'];
            }
        } else {
            return null;
        }
    }

    public function set($key, $value, $expire)
    {
        $sql = "replace into `{$this->_name}` values (:key, :value, :expire)";
        
        $stmt = $this->getAdapter()->prepare($sql);
        
        $res = $stmt->execute(array(
            ':key' => $key,
            ':value' => $value,
            ':expire' => intval($expire)
        ));
        
        return (bool) $res;
    }

    public function isValidKey($key)
    {
        $res = $this->get($key);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}