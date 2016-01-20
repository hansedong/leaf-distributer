<?php

namespace Leaf\Distributer;

/**
 * Class Autoloader
 * 自动加载器
 *
 * @package Leaf\Distributer
 */
class Autoloader
{

    /**
     * 倘若你的项目里没有使用composer，那么也依然可以用本组件，只需要调用下面的方法，注册一个自动加载机制即可。
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