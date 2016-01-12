<?php

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Loger\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Distribution\DistriManager;

$distriManager = new DistriManager();

//定义配置
$arrConfig = [
    'write' => [
        [
            'host' => 'x.x.x.1',
            'port' => 6379,
            'db'   => 1,
        ],
        [
            'host' => 'x.x.x.2',
            'port' => 6379,
            'db'   => 1,
        ],
    ],
    'read'  => [
        [
            'host' => 'x.x.x.3',
            'port' => 6379,
            'db'   => 1,
        ],
        [
            'host' => 'x.x.x.4',
            'port' => 6379,
            'db'   => 1,
        ],
    ],
];

//获取一致性哈希的处理器
$distriHandler = $distriManager->getDistributer()->instanceHandler($arrConfig)->lookUp('aaa');


