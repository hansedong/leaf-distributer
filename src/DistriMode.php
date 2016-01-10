<?php

namespace Leaf\Distribution;

/**
 * Class DistriMode
 *
 * @package Leaf\Distribution
 */
class DistriMode
{

    /**
     * 分布式算法
     */
    const DIS_CONSISTENT_HASHING = 1;

    /**
     * 取模算法
     */
    const DIS_MODULO = 2;

    /**
     * 可用的分布式标识
     *
     * @var array
     */
    public static $arrDistriMode = [self::DIS_CONSISTENT_HASHING, self::DIS_MODULO];

}