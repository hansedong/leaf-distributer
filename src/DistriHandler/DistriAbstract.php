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
     * the config group of a cluster
     *
     * @var array
     */
    protected $clusterConfig = [];

    /**
     * server objects that have instantiated and connected to the real servers
     *
     * @var array
     */
    protected $keepAliveServers = [];

    /**
     * 根据key，获取具体的配置
     *
     * @param string $key
     *
     * @return array
     */
    abstract public function lookUp($key = '');

    /**
     * set the configure of this distributer handler
     *
     * @param array $configGroup
     *
     * @return $this
     */
    protected function setConfig(array $configGroup = [])
    {
        if ( !empty( $configGroup )) {
            $this->clusterConfig = $configGroup;
        }
        else {
            throw new \InvalidArgumentException('set the config of distributer handler error! the config can not be empty!');
        }

        return $this;
    }

    abstract public function init(array $config);

}