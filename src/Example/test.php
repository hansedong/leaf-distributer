<?php

function crc64Table()
{
        $crc64tab = [];

            // ECMA polynomial
                $poly64rev = (0xC96C5795 << 32) | 0xD7870F42;

                    // ISO polynomial
                        // $poly64rev = (0xD8 << 56);

                            for ($i = 0; $i < 256; $i++)
                                    {
                                                for ($part = $i, $bit = 0; $bit < 8; $bit++) {
                                                                if ($part & 1) {
                                                                                    $part = (($part >> 1) & ~(0x8 << 60)) ^ $poly64rev;
                                                                                                } else {
                                                                                                                    $part = ($part >> 1) & ~(0x8 << 60);
                                                                                                                                }
                                                                                                                                        }

                                                                                                                                               $crc64tab[$i] = $part;
                                                                                                                                                   }

                                                                                                                                                       return $crc64tab;
}


echo crc64Table('');

function crc64($string, $format = '%x')
{
        static $crc64tab;

            if ($crc64tab === null) {
                        $crc64tab = crc64Table();
                            }

                                $crc = 0;

                                    for ($i = 0; $i < strlen($string); $i++) {
                                                $crc = $crc64tab[($crc ^ ord($string[$i])) & 0xff] ^ (($crc >> 8) & ~(0xff << 56));
                                                    }

                                                        return sprintf($format, $crc);
}


