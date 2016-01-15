<?php

namespace Leaf\Distributer\Algorithm;

class Hashing
{


    /**
     * get hash value of a string
     *
     * @param string $str
     * @param string $hashType
     *
     * @return string
     */
    public static function hash($str, $hashType = HashMode::MD5)
    {
        return hash($str, $hashType);
    }

}
