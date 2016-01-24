<?php

namespace Leaf\Distributer\DistriHandler;

use Leaf\Distributer\Algorithm\Hashing;
use Leaf\Distributer\Algorithm\HashMode;
use Leaf\Distributer\Algorithm\Search;

/**
 * Class ConsistentHashing
 * 一致性哈希算法处理器
 *
 * @package Leaf\Distributer\DistriHandler
 */
class ConsistentHashing extends DistriAbstract
{

    /**
     * 每一级权重对应的节点数量
     * 如果你的配置中，某一组权重设置为1，则这组将来会分配默认（160）节点数量的虚拟节点。如果你设置为2，则虚拟节点数量为320。以此类推
     *
     * @var int
     */
    protected $baseNodeNum = 160;

    /**
     * 每个集群配置下的写节点的hash标识
     *
     * @var array
     */
    protected $clusterWriteNodes = [];

    /**
     * 每个集群配置下读节点的hash标识
     * The nodes of the server that can be accessed for reading data only.
     *
     * @var array
     */
    protected $clusterReadNodes = [];

    /**
     * 本哈希树立起是否已经初始化过了，1是已经做了初始化，0为没有初始化
     *
     * @var int
     */
    protected $clusterConfigInitStatus = 0;

    /**
     * 虚拟节点组成的哈希环
     * 你需要了解的是，哈希环上的节点标识是标示同一个集群下的不同的配置组的
     *
     * @var array
     */
    protected $clusterHashNodes = [];

    /**
     * 每一个真实的server配置对应的hash值。数组类型，key是哈希值，value是配置信息
     *
     * @var array
     */
    protected $serverHashConfigMap = [];

    /**
     * 哈希环上节点的值的生成算法，默认是sha384算法
     *
     * @var string
     */
    protected $hashStringFunc = HashMode::STR_SHA384;

    /**
     * 根据key，查找具体的server配置信息
     *
     * @param string $key  要查找配置信息的Key
     * @param string $from 取读配置还是写配置
     *
     * @return array
     */
    public function lookUp($key = '', $from = 'read')
    {
        //获取虚拟节点组成的哈希的整型keys
        if (empty( $this->circlehashNodesKeys )) {
            $circleHashNodesIntkeys = array_keys($this->clusterHashNodes);
            $this->circlehashNodesKeys = $circleHashNodesIntkeys;
        }
        else {
            $circleHashNodesIntkeys = $this->circlehashNodesKeys;
        }
        //将要查找配置信息的key转换为整型
        $intvalKey = Hashing::hashToNumberic($key, HashMode::NUM_CRC32);
        /**
         * 获取配置对应的hash值，并根据配置的hash值，定位确定的配置信息
         */
        //二分法查找key从哈希环上对应的一个合适的整型数值
        $findNodeKey = Search::dichotomizingSearch($circleHashNodesIntkeys, $intvalKey);
        //根据得到的整型数值，从哈希环上找到这个数值对应的配置的hash值
        if ( !empty( $this->clusterHashNodes[$findNodeKey] )) {
            $clusterNodeValue = $this->clusterHashNodes[$findNodeKey];
        }
        else {
            throw new \RuntimeException('fatal error occured when loopup config for key: ' . $key);
        }

        //根据配置的hash值，获取读类型或者写类型的配置
        return $this->getConfigByHashNode($clusterNodeValue, $from);
    }

    /**
     * 根据哈希环上的节点的hash值，取得这个hash值对应的真实配置
     * 由于hash值对应的是一组读写对应的配置，所以，你需要指定获取这个组中的读配置还是写配置
     *
     * @param string $clusterNodeValue 虚拟节点上的hash值
     * @param string $from             配置类型，可以为：read或者write
     *
     * @return mixed
     */
    protected function getConfigByHashNode($clusterNodeValue, $from = 'read')
    {
        if ($from === 'read') {
            $randomKey = array_rand($this->clusterReadNodes[$clusterNodeValue]);
            $serverNodeValue = $this->clusterReadNodes[$clusterNodeValue][$randomKey];
        }
        else {
            $randomKey = array_rand($this->clusterWriteNodes[$clusterNodeValue]);
            $serverNodeValue = $this->clusterWriteNodes[$clusterNodeValue][$randomKey];
        }

        return $this->serverHashConfigMap[$serverNodeValue];
    }

    /**
     * 对当前算法处理器做初始化操作
     *
     * @param array $clusterConfig
     *
     * @throws \Exception
     */
    public function init(array $clusterConfig = [])
    {
        try {
            if ( !empty( $clusterConfig )) {
                //设置配置
                $this->setConfig($clusterConfig);
                //生成配置节点
                $this->makeServerNodes();
            }
            else {
                throw new \InvalidArgumentException('param error,clusterConfig can\' be empty!');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 生成哈希环
     *
     * @return $this
     */
    protected function makeServerNodes()
    {
        try {
            if ( !empty( $this->clusterConfig )) {
                foreach ($this->clusterConfig as $clusterName => $currentClusterConfig) {
                    //为集群配置创建hash值
                    $clusterHashKey = Hashing::hashToStr($clusterName, $this->hashStringFunc);
                    if (empty( $clusterHashKey )) {
                        throw new \RuntimeException('Make Cluster Key error! The config of the cluster may be has something wrong!');
                    }
                    //初始化集群中的write节点和read节点
                    $this->initNodes($clusterHashKey, $currentClusterConfig);
                    //初始化虚拟几点，生成哈希环
                    $virtualNodeCount = !empty( $currentClusterConfig['weight'] ) ? $currentClusterConfig['weight'] * $this->baseNodeNum : $this->baseNodeNum;
                    for ($i = 0; $i < $virtualNodeCount; $i++) {
                        $iNodeKey = Hashing::hashToNumberic($clusterHashKey . '_' . $i, HashMode::NUM_CRC32);
                        $this->clusterHashNodes[$iNodeKey] = $clusterHashKey;
                    }
                    $arrConfigInfo = null;
                }
                $this->clusterConfigInitStatus = 1;
            }
            else {
                throw new \RuntimeException('Invalid config of the cluster, please check the config you setted of the cluster');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
        //set the zero key with the value of this first of  $this->clusterHashNodes
        //$this->clusterHashNodes[0] = array_slice($this->clusterHashNodes, 0, 1)[0];
        //按Key升序排序
        ksort($this->clusterHashNodes);

        return $this;
    }

    /**
     * 为具体的配置信息生成唯一哈希值
     * 默认使用Sha384算法，不采用Md5是因为Md5发生hash碰撞的概率大
     *
     * @param array $config
     *
     * @return null|string
     */
    protected function makeHashKeyOfConfig(array $config = [])
    {
        $return = null;
        if ( !empty( $config )) {
            ksort($config);
            $return = Hashing::hashToStr(http_build_query($config), HashMode::STR_SHA384);
        }

        return $return;
    }

    /**
     * 初始化哈希环上的虚拟节点
     *
     *
     * @param string $clusterHashKey       以集群为单位的的哈希值
     * @param array  $currentClusterConfig 这个集群的配置
     *
     * @return $this
     */
    protected function initNodes($clusterHashKey, array $currentClusterConfig)
    {
        foreach ($currentClusterConfig as $type => $serverConfigs) {
            if (is_array($serverConfigs) && !empty( $serverConfigs )) {
                foreach ($serverConfigs as $config) {
                    //为具体的配置信息生成唯一哈希值
                    $nodeHashKey = $this->makeHashKeyOfConfig($config);
                    //保存真实配置和其hash值的对应字典
                    $this->serverHashConfigMap[$nodeHashKey] = $config;
                    //根据权重，初始化每个集群中的读节点和写节点
                    $weight = !empty( $config['weight'] ) ? intval($config['weight']) : 1;
                    if ($type === 'write') {
                        if (empty( $this->clusterWriteNodes[$clusterHashKey] )) {
                            $this->clusterWriteNodes[$clusterHashKey] = [];
                        }
                        for ($i = 0; $i < $weight; $i++) {
                            $this->clusterWriteNodes[$clusterHashKey][] = $nodeHashKey;
                        }
                    }
                    elseif ($type === 'read') {
                        if (empty( $this->clusterReadNodes[$clusterHashKey] )) {
                            $this->clusterReadNodes[$clusterHashKey] = [];
                        }
                        for ($i = 0; $i < $weight; $i++) {
                            $this->clusterReadNodes[$clusterHashKey][] = $nodeHashKey;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * 指定生成哈喜欢上节点的值的哈希函数
     *
     * @param string $hashStringFunc
     *
     * @return $this
     */
    public function setHashFunc($hashStringFunc = HashMode::STR_SHA384)
    {
        if ( !empty( $hashStringFunc ) && is_string($hashStringFunc)) {
            $this->hashStringFunc = $hashStringFunc;
        }

        return $this;
    }

}