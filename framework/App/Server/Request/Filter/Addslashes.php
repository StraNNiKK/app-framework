<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Server
 * @subpackage Request
 * @version    $Id:$
 */

/**
 * Фильтр для экранирования строки слешами
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Request_Filter_Addslashes
{

    /**
     * Метод, реализующий фильтрацию
     *
     * @param string $entity            
     * @return string
     */
    public function filter($entity)
    {
        return addslashes($entity);
    }
}