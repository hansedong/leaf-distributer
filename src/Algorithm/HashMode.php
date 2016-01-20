<?php

namespace Leaf\Distributer\Algorithm;

/**
 * Class HashMode
 * 哈希算法的类型
 *
 * @package Leaf\Distributer\Algorithm
 */
class HashMode
{

    /**
     * 将字符串转换为hash值的常量
     */
    const STR_MD5 = 'md5';
    const STR_SHA1 = 'sha1';
    const STR_SHA256 = 'sha256';
    const STR_SHA384 = 'sha384';
    const STR_SHA512 = 'sha512';
    const STR_RIPEMD128 = 'ripemd128';
    const STR_RIPEMD160 = 'ripemd160';
    const STR_RIPEMD256 = 'ripemd256';
    const STR_RIPEMD320 = 'ripemd320';
    const STR_SWHIRLPOOL = 'whirlpool';
    const STR_SNEFRU = 'snefru';
    const STR_GOST = 'gost';
    const STR_ADLER32 = 'adler32';
    const STR_CRC32 = 'crc32';
    const STR_CRC32B = 'crc32b';

    /**
     * 将字符串转换为整型的常量
     */
    const NUM_CRC32 = 'crc32';
    const NUM_CRC64 = 'crc64';

}
