<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Function
 * @version    $Id:$
 */
defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/');
require_once APP_FRAMEWORK_MAIN_DIR . 'Debug.php';

class App_Functions
{
}


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