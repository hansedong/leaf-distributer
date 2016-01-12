<?php

namespace Leaf\Distribution;

use Leaf\Distribution\DistriHandler\ConsistentHashing;
use Leaf\Distribution\DistriHandler\Modulo;

/**
 * Class DistriMode
 * The distribution type class
 *
 * @package Leaf\Distribution
 */
class DistriMode
{

    /**
     * Consistent hashing algorithm
     *
     * @var int
     */
    const DIS_CONSISTENT_HASHING = 1;

    /**
     * Modulo algorithm
     *
     * @var int
     */
    const DIS_MODULO = 2;

    /**
     * Identifications of distribution algorithms
     *
     * @var array
     */
    public static $arrDistriModeClass = [
        self::DIS_CONSISTENT_HASHING => ConsistentHashing::class,
        self::DIS_MODULO             => Modulo::class,
    ];

    /**
     * Get the distribution algorithm class name according to the identification
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
     * Register a customed distribution algorithm class to extend this class.
     *
     * @param int    $mode      The identification of your customed distribution algorithm. It can only be a integer.
     * @param string $className The class name with it's namespace.
     *
     * @return bool
     */
    public static function registerDistriModeClass($mode, $className)
    {
        $bool = false;
        //validate params
        if ( !is_integer($mode) || array_key_exists($mode,
                static::$arrDistriModeClass) || !is_string($className) || empty( $className )
        ) {
            throw new \InvalidArgumentException('register distributer error! param mode must be integer, param className can\'be empty!');
        }
        static::$arrDistriModeClass[$mode] = $className;
        $bool = true;

        return $bool;
    }

}