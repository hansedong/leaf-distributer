<?php

namespace Leaf\Distributer\DistriHandler;

use Leaf\Distributer\Algorithm\Hashing;
use Leaf\Distributer\Algorithm\HashMode;

class ConsistentHashing extends DistriAbstract
{

    /**
     * the node number of a server
     *
     * @var int
     */
    protected $baseNodeNum = 160;

    protected $clusterConfigInitStatus = 0;

    protected $clusterHashNodes = [];
    protected $clusterWriteNodes = [];
    protected $clusterReadNodes = [];

    /**
     * The nodes of the hash circle
     *
     * @var array
     */
    protected $circleHashNodes = [];

    /**
     * a map of K=>V structure, Value is a server config, Key is the hash name of the Value.
     *
     * @var array
     */
    protected $serverHashConfigMap = [];

    public function lookUp($key = '')
    {
        // TODO: Implement lookUp() method.
    }

    /**
     * init this algorithm
     *
     * @param array $clusterConfig
     *
     * @throws \Exception
     */
    public function init(array $clusterConfig = [])
    {
        try {
            if ( !empty( $clusterConfig )) {
                //set the configure of the distribution handler
                $this->setConfig($clusterConfig);
                //create server node
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
     * Make hash keys to mark the config groups
     * Choose every config groups's config with the write type attribute to make the hash key
     *
     * @return $this
     */
    protected function makeServerNodes()
    {
        try {
            if ( !empty( $this->clusterConfig )) {
                $arrNewConfigs = [];
                foreach ($this->clusterConfig as $currentClusterConfig) {
                    //make a hash key for a cluster
                    $clusterHashKey = $this->makeClusterHashKey($currentClusterConfig);
                    if (empty( $clusterHashKey )) {
                        throw new \RuntimeException('Make Cluster Key error! The config of the cluster may be has something wrong!');
                    }
                    //init nodes
                    $this->initNodes($clusterHashKey, $currentClusterConfig);
                    //init virtual nodes
                    $virtualNodeCount = !empty( $currentClusterConfig['weight'] ) ? $currentClusterConfig['weight'] * $this->baseNodeNum : $this->baseNodeNum;
                    for ($i = 0; $i < $virtualNodeCount; $i++) {
                        $iNodeKey = Hashing::hash($clusterHashKey . '_' . $i, HashMode::CRC32);
                        $this->circleHashNodes[$iNodeKey] = $clusterHashKey;
                    }
                    $arrConfigInfo = null;
                    break;
                }
                $this->clusterConfig = $arrNewConfigs;
                $this->clusterConfigInitStatus = 1;
            }
            else {
                throw new \RuntimeException('Invalid config of the cluster, please check the config you setted of the cluster');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
        //set the zero key with the value of this first of  $this->circleHashNodes
        $this->circleHashNodes[0] = array_slice($this->circleHashNodes, 0, 1)[0];
        //ksort
        ksort($this->circleHashNodes);

        var_dump($this->clusterWriteNodes);
        var_dump($this->clusterReadNodes);

        return $this;
    }

    /**
     * Make the cluster key of a cluster
     *
     * @param array $clusterConfig
     *
     * @return string
     */
    protected function makeClusterHashKey(array $clusterConfig)
    {
        //get the first config of the write group
        $arrWriteConfigInfo = !empty( $clusterConfig['write'][0] ) ? $clusterConfig['write'][0] : [];
        if (empty( $arrWriteConfigInfo )) {
            throw new \RuntimeException('You may have a invalid config');
        }
        $clusterHashKey = $this->makeHashKeyOfConfig($arrWriteConfigInfo);

        return $clusterHashKey;
    }

    protected function makeHashKeyOfConfig(array $config = [])
    {
        $return = null;
        if ( !empty( $config )) {
            ksort($config);
            $return = Hashing::hash(http_build_query($config), HashMode::MD5);
        }

        return $return;
    }

    /**
     * Init distribution nodes
     *
     *
     * @param string $clusterHashKey
     * @param array  $currentClusterConfig
     */
    protected function initNodes($clusterHashKey, array $currentClusterConfig)
    {
        foreach ($currentClusterConfig as $type => $serverConfigs) {
            if (is_array($serverConfigs) && !empty( $serverConfigs )) {
                foreach ($serverConfigs as $config) {
                    //get hash key of the config
                    $nodeHashKey = $this->makeHashKeyOfConfig($config);
                    //init hash map of server configs
                    $this->serverHashConfigMap[$nodeHashKey] = $config;
                    //init clusterWriteNodes and clusterReadNodes
                    $weight = !empty( $config['weight'] ) ? intval($config['weight']) : 1;
                    $type = $config['type'];
                    if ($type === 'write') {
                        if ( !empty( $this->clusterWriteNodes[$clusterHashKey] )) {
                            $this->clusterWriteNodes[$clusterHashKey] = [];
                        }
                        for ($i = 0; $i < $weight; $i++) {
                            $this->clusterWriteNodes[] = $nodeHashKey;
                        }
                    }
                    elseif ($type === 'read') {
                        if ( !empty( $this->clusterReadNodes[$clusterHashKey] )) {
                            $this->clusterReadNodes[$clusterHashKey] = [];
                        }
                        for ($i = 0; $i < $weight; $i++) {
                            $this->clusterReadNodes[] = $nodeHashKey;
                        }
                    }
                }
                if ($type === 'write') {

                }
                elseif ($type == 'read') {
                }
            }
        }
    }

}