<?php

interface App_Resource_Store_Interface
{

    public function init();

    public function set($key, $value);

    public function get($key);
}