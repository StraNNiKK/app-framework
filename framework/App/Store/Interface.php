<?php

interface App_Store_Interface
{

    public function get($key);

    public function set($key, $value);

    public function isValidKey($key);
}