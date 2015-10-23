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
 * Фильтр - применение htmlspecialchars к строке
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Request_Filter_Htmlspecialchars
{

    /**
     * Метод, реализующий фильтрацию
     *
     * @param string $entity            
     * @return string
     */
    public function filter($entity)
    {
        return htmlspecialchars($entity);
    }
}