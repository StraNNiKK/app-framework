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
 * Фильтр - применение htmlentities к строке
 *
 * @category App
 * @package App_Server
 * @subpackage Request
 */
class App_Server_Request_Filter_Htmlentities
{

    /**
     * Метод, реализующий фильтрацию
     *
     * @param string $entity            
     * @return string
     */
    public function filter($entity)
    {
        return htmlentities($entity);
    }
}