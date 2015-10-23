<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Function
 * @version    $Id:$
 */
require_once 'App/Debug.php';

/**
 * Более короткий псевдоним для App_Debug::show()
 *
 * @param mixed $var            
 * @param boolean $echo            
 * @return string
 */
function d($var, $echo = true)
{
    return App_Debug::show($var, $echo);
}

function console($something)
{
    return App_Debug::log($something);
}