<?php

namespace Leaf\Distributer\Algorithm;

/**
 * Class Hashing
 * 哈希算法
 *
 * @package Leaf\Distributer\Algorithm
 */
class Hashing
{

    /**
     * 将一个字符串转换为一个字符串的哈希值
     *
     * @param string $str      被转换的字符串
     * @param string $hashType 采用何种哈希函数来转换，默认是MD5算法
     *
     * @return string
     */
    public static function hashToStr($str, $hashType = HashMode::STR_MD5)
    {
        return hash($hashType, $str);
    }

    /**
     * 将一个字符串转换为一个数值类型
     *
     * @param string $str      被转换的字符串
     * @param string $hashType 采用哈中哈希函数转换，默认是CRC64
     *
     * @return int|float
     */
    public static function hashToNumberic($str, $hashType = HashMode::NUM_CRC64)
    {
        if (method_exists(static::class, $hashType)) {
            return call_user_func("static::" . $hashType, $str);
        }
        else {
            throw new \BadFunctionCallException('You can\'t call a method not exists');
        }
    }

    /**
     * CRC32算法
     *
     * @param $str
     *
     * @return float
     */
    protected static function crc32($str)
    {
        $return = crc32($str);

        return floatval(sprintf("%u\n", $return));
    }

    /**
     * CRC64算法
     *
     * @param string $string
     * @param string $format
     *
     * @return mixed
     *
     * Formats:
     *  crc64('php'); // afe4e823e7cef190
     *  crc64('php', '0x%x'); // 0xafe4e823e7cef190
     *  crc64('php', '0x%X'); // 0xAFE4E823E7CEF190
     *  crc64('php', '%d'); // -5772233581471534704 signed int
     *  crc64('php', '%u'); // 12674510492238016912 unsigned int
     */
    public static function crc64($string, $format = '%u')
    {
        static $crc64tab;
        if ($crc64tab === null) {
            $crc64tab = static::crc64Table();
        }
        $crc = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $crc = $crc64tab[( $crc ^ ord($string[$i]) ) & 0xff] ^ ( ( $crc >> 8 ) & ~( 0xff << 56 ) );
        }
        $return = sprintf($format, $crc);
        if ($format === '%u') {
            $return = floatval($return);
        }

        return $return;
    }

    /**
     * @return array
     */
    protected static function crc64Table()
    {
        $crc64tab = [];
        // ECMA polynomial
        $poly64rev = ( 0xC96C5795 << 32 ) | 0xD7870F42;
        // ISO polynomial
        // $poly64rev = (0xD8 << 56);
        for ($i = 0; $i < 256; $i++) {
            for ($part = $i, $bit = 0; $bit < 8; $bit++) {
                if ($part & 1) {
                    $part = ( ( $part >> 1 ) & ~( 0x8 << 60 ) ) ^ $poly64rev;
                }
                else {
                    $part = ( $part >> 1 ) & ~( 0x8 << 60 );
                }
            }
            $crc64tab[$i] = $part;
        }

        return $crc64tab;
    }

}
