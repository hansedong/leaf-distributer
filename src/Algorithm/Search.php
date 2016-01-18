<?php

namespace Leaf\Distributer\Algorithm;

/**
 * Class Search
 *
 * @package Leaf\Distributer\Algorithm
 */
class Search
{

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

    public static function dichotomizingSearch($arr, $num)
    {
        $twoElementArr = static::dichotomizingSearchArray($arr, $num);
        $element = array_pop($twoElementArr);
        if ($element <= $num) {
            return $element;
        }
        else {
            $element = array_pop($twoElementArr);

            return $element;
        }
    }

}
