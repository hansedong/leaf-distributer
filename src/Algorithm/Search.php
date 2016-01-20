<?php

namespace Leaf\Distributer\Algorithm;

/**
 * Class Search
 * 查找算法
 *
 * @package Leaf\Distributer\Algorithm
 */
class Search
{

    /**
     * 使用二分法查找。从一个数组中，不断过滤，筛选出一个二维数组，二维素组的最小值必是<=你要查找的值的,且与你提供的数值最为接近
     * 比如有数组[1,3,5,7,9]，你提供的值如果为7，则找到的数组为：[7,9]，如果你提供的是2，则找到的数组为：[1,3]
     *
     * @param array $arr 原始数组
     * @param int   $num 要查找的值
     *
     * @return array
     */
    public static function dichotomizingSearchArray($arr, $num)
    {
        $count = count($arr);
        $middleKey = ( $count % 2 === 0 ) ? $count / 2 : ( $count + 1 ) / 2;
        if ($arr[$middleKey] > $num) {
            $splitArr = array_slice($arr, 0, $middleKey);
        }
        else {
            $splitArr = array_slice($arr, $middleKey, $count);
        }
        if (count($splitArr) <= 2) {
            return $splitArr;
        }
        else {
            return static::dichotomizingSearchArray($splitArr, $num);
        }
    }

    /**
     * 从一个二维数组中，找到一个元素。这个元素刚好<=你提供的数值
     *
     * @param array $arr
     * @param int   $num
     *
     * @return int
     */
    public static function dichotomizingSearch(array $arr, $num)
    {
        $twoElementArr = static::dichotomizingSearchArray($arr, $num);
        $element = array_pop($twoElementArr);
        if ($element <= $num) {
            $return = $element;
        }
        else {
            $element = array_pop($twoElementArr);
            $return = $element;
        }

        return $return;
    }

}
