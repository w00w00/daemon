<?php
namespace Daemon\System;

class Autoloader
{
    public static function start()
    {
        spl_autoload_register(function($class) {
            $className = str_replace("\\", DIRECTORY_SEPARATOR, $class);
            require_once $className . '.php';
        });
    }
}