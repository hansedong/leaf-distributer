<?php

namespace Leaf\Distribution\DistriHandler;


abstract class DistriAbstract
{

    protected $arrConfigGroup = [];

    /**
     * 根据key，获取具体的配置
     *
     * @param string $key
     *
     * @return array
     */
    abstract public function lookUp($key = '');

    /**
     * set the config of this distributer handler
     *
     * @param array $arrConfigGroup
     *
     * @return $this
     */
    public function setConfig(array $arrConfigGroup = [])
    {
        if ( !empty( $arrConfigGroup )) {
            $this->arrConfigGroup = $arrConfigGroup;
        }
        else {
            throw new \InvalidArgumentException('set the config of distributer handler error! the config can not be empty!');
        }

        return $this;
    }

    /**
     * 根据配置组，将配置组转换为唯一的配置名
     *
     * @param array $arrConfigGroup [ ['host'=>'192.168.200.85','port'=>6379,..],
     *                              ['host'=>'192.168.200.85','port'=>6379,..],... ]
     *
     * @return string
     * @throws \Exception
     */
    private function getConfigUniqueKey(array $arrConfigGroup = [])
    {
        $return = '';
        if ( !empty( $arrConfigGroup )) {
            $strKey = '';
            //遍历数组，拼接子数组的key和val，组成一个字符串，用于hash
            $arrString = [];
            foreach ($arrConfigGroup as $key => &$arrConfig) {
                if (is_array($arrConfig) && !empty( $arrConfig )) {
                    ksort($arrConfig);
                    $arrString[] = http_build_str($arrConfig);
                    $arrConfig = null;
                }
                else {
                    throw new \InvalidArgumentException("sub config error");
                }
            }
            if ( !empty( $arrString )) {
                $return = implode(';', $arrString);
                $arrString = null;
            }
        }
        else {
            throw new \InvalidArgumentException("config error");
        }

        return $return;
    }
}