<?php

namespace Leaf\Distributer;

class Autoloader
{

    /**
     * register a autoloader for the situation that you don't have the php composer so that you can also use this
     * package
     */
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'Leaf\\Distributer';
            $base_dir = __DIR__;
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        });
    }

}