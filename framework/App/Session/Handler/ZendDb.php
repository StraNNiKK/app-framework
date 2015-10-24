<?php
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Session/Handler/Interface.php';


class App_Session_Handler_ZendDb implements App_Session_Handler_Interface
{

    protected static $_sessObj = null;

    protected static $_tableName = null;

    protected function _initModel()
    {
        if (! self::$_tableName) {
            $config = App_Server::getInstance()->getConfig();
            self::$_tableName = $config['store']['session']['tableName'];
        }
        
        if (! self::$_sessObj) {
            self::$_sessObj = App_Zend_Model::getModelAdapter();
        }
    }

    public function open($savePath, $name)
    {
        $id = session_id();
        
        try {
            $this->_initModel();
            
            $params = session_get_cookie_params();
            
            $result = self::$_sessObj->update(self::$_tableName, array(
                'expires' => time() + $params['lifetime']
            ), self::$_sessObj->quoteInto('id = ?', $id));
            
            if (intval($result) < 1) {
                self::$_sessObj->insert(self::$_tableName, array(
                    'id' => $id,
                    'expires' => time() + $params['lifetime']
                ));
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function close()
    {
        // because on this step current dir can take different values
        // see bug #39619: http://bugs.php.net/bug.php?id=39619
        chdir(BOOT_PATH);
        
        self::$_sessObj = null;
        return true;
    }

    public function read($id)
    {
        $obj = self::$_sessObj->fetchRow('SELECT * FROM `' . self::$_tableName . '` WHERE ' . self::$_sessObj->quoteInto('id = ?', $id));
        
        if ($obj) {
            return $obj['sessionData'];
        } else {
            return '';
        }
    }

    public function write($id, $data)
    {
        // because on this step current dir can take different values
        // see bug #39619: http://bugs.php.net/bug.php?id=39619
        chdir(BOOT_PATH);
        
        $params = session_get_cookie_params();
        
        $sql = 'REPLACE INTO `' . self::$_tableName . '` VALUES (:id, :expires, :sessionData)';
        
        $stmt = self::$_sessObj->prepare($sql);
        
        $res = $stmt->execute(array(
            ':id' => $id,
            ':expires' => time() + $params['lifetime'],
            ':sessionData' => $data
        ));
        
        return (bool) $res;
    }

    public function destroy($id)
    {
        $sql = 'DELETE FROM `' . self::$_tableName . '` WHERE id = :id';
        
        $stmt = self::$_sessObj->prepare($sql);
        $res = $stmt->execute(array(
            ':id' => $id
        ));
        
        return (bool) $res;
    }

    public function gc($maxlifetime)
    {
        self::$_sessObj->delete(self::$_tableName, 'expires < ' . time());
    }
}