<?php
require_once 'App/Event/Dispatcher.php';

abstract class App_Event_Observable_Abstract
{

    public function fire($eventName)
    {
        App_Event_Dispatcher::getInstance()->fire($eventName, $this);
    }

    public function addEvent($eventName)
    {
        App_Event_Dispatcher::getInstance()->addEvent($eventName, $this);
    }

    public function sign($eventName, array $observer)
    {
        App_Event_Dispatcher::getInstance()->sign($eventName, $observer, $this);
    }
}