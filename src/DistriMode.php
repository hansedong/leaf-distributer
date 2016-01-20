<?php

namespace Leaf\Distributer;

use Leaf\Distributer\DistriHandler\ConsistentHashing;
use Leaf\Distributer\DistriHandler\Modulo;

/**
 * Class DistriMode
 *
 * 分布式算法类型
 * 可拓展，你可以自己写信的算法到Algorithm下，以解决当前组件的算法无法满足你的需求的情况。写完之后，用此类注册。详见 READEME.MD
 *
 * @package Leaf\Distributer
 */
class DistriMode
{

    /**
     * 一致性哈希算法
     *
     * @var int
     */
    const DIS_CONSISTENT_HASHING = 1;

    /**
     * 取模算法
     *
     * @var int
     */
    const DIS_MODULO = 2;

    /**
     * 分布式算法标识和对应的算法处理类
     *
     * @var array
     */
    public static $arrDistriModeClass = [
        self::DIS_CONSISTENT_HASHING => ConsistentHashing::class,
        self::DIS_MODULO             => Modulo::class,
    ];

    /**
     * 根据分布式算法标识，获取分布式算法的处理类名
     *
     * @param int $mode
     *
     * @return string|null
     */
    public static function getDistriModeClass($mode = self::DIS_CONSISTENT_HASHING)
    {
        $class = null;
        if (array_key_exists($mode, static::$arrDistriModeClass)) {
            $class = static::$arrDistriModeClass[$mode];
        }

        return $class;
    }

    /**
     * 注册一个自定义的分布式算法类
     * 用于拓展用，适用于你觉得当前组件无法满足你的需求，你需要自写分布式算法的情形
     *
     * @param int    $mode      你要拓展的分布式算法的标识。整型且不能为1和2，因为这2个数字已经被当前组件用了
     * @param string $className 类名（带命名空间的）
     *
     * @return bool
     */
    public static function registerDistriModeClass($mode, $className)
    {
        $bool = false;
        //参数验证
        if ( !is_integer($mode) || array_key_exists($mode, static::$arrDistriModeClass) || !is_string($className) || empty( $className )) {
            throw new \InvalidArgumentException('register distributer error! param mode must be integer, param className can\'be empty!');
        }
        //维护用户自定义的算法
        static::$arrDistriModeClass[$mode] = $className;
        $bool = true;

        return $bool;
    }

}