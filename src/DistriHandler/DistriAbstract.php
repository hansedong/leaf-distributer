<?php

namespace Leaf\Distributer\DistriHandler;

/**
 * Class DistriAbstract
 * 1.set config of this handler
 * 2.init the handler
 * 3.lookUp to find the config of a server node
 *
 * @package Leaf\Distributer\DistriHandler
 */
abstract class DistriAbstract
{

    /**
     * 当前分布式算法处理器所处理的原始集群配置信息
     *
     * @var array
     */
    public $clusterConfig = [];

    /**
     * 根据key，查找具体的server配置信息
     *
     * @param string $key  要查找配置的key
     * @param string $from 查找配置的类型，读配置还是写配置，默认为读配置read
     *
     * @return array
     */
    abstract public function lookUp($key = '', $from = 'read');

    /**
     * 为当前分布式算法处理器保存原始集群配置信息
     *
     * @param array $clusterConfig
     *
     * @return $this
     */
    protected function setConfig(array $clusterConfig = [])
    {
        if ( !empty( $clusterConfig )) {
            $this->clusterConfig = $clusterConfig;
        }
        else {
            throw new \InvalidArgumentException('set the config of distributer handler error! the config can not be empty!');
        }

        return $this;
    }

    abstract public function init(array $config);

}