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
    public static function hash($hashType = HashMode::MD5, $str)
    {
        return hash($str, $hashType);
    }

}
