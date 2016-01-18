<?php

namespace Leaf\Distributer\DistriHandler;

use Leaf\Distributer\Algorithm\Hashing;
use Leaf\Distributer\Algorithm\HashMode;
use Leaf\Distributer\Algorithm\Search;

class ConsistentHashing extends DistriAbstract
{

    /**
     * The node number of a cluster.
     * If the weight value is setted as 1, the cluster will be distributed to a base number nodes(160). If is setted
     * as 2, the nodes double of the number(320). if is setted as 3, then the nodes triple the number(480) and go on.
     *
     * @var int
     */
    protected $baseNodeNum = 160;

    /**
     * The nodes of the server that can be accessed for writing data.
     *
     * @var array
     */
    protected $clusterWriteNodes = [];

    /**
     * The nodes of the server that can be accessed for reading data only.
     *
     * @var array
     */
    protected $clusterReadNodes = [];

    protected $clusterConfigInitStatus = 0;

    /**
     * The distributed nodes of all the cluster
     * You should know exactly that the hash nodes are based on cluster, not the specific server.
     *
     * @var array
     */
    protected $clusterHashNodes = [];

    /**
     * a map of K=>V structure, Value is a server config, Key is the hash name of the Value.
     *
     * @var array
     */
    protected $serverHashConfigMap = [];

    /**
     * look up a server config of a cluster by a key
     *
     * @param string $key  key name
     * @param string $from read|write.
     *
     * @return array
     */
    public function lookUp($key = '', $from = 'read')
    {
        if (empty( $this->circlehashNodesKeys )) {
            $circleHashNodesIntkeys = array_keys($this->clusterHashNodes);
            $this->circlehashNodesKeys = $circleHashNodesIntkeys;
        }
        else {
            $circleHashNodesIntkeys = $this->circlehashNodesKeys;
        }
        //convert a string key to a numberic value
        $intvalKey = Hashing::hashToNumberic($key, HashMode::NUM_CRC32);
        //find the node that the param $key to use
        $findNodeKey = Search::dichotomizingSearch($circleHashNodesIntkeys, $intvalKey);
        if ( !empty( $this->clusterHashNodes[$findNodeKey] )) {
            $clusterNodeValue = $this->clusterHashNodes[$findNodeKey];
        }
        else {
            throw new \RuntimeException('fatal error occured when loopup config for key: ' . $key);
        }

        return $this->getConfigByHashNode($clusterNodeValue, $from);
    }

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
                foreach ($this->clusterConfig as $clusterName => $currentClusterConfig) {
                    //make a hash key for a cluster
                    $clusterHashKey = Hashing::hashToStr($clusterName, HashMode::STR_SHA384);
                    if (empty( $clusterHashKey )) {
                        throw new \RuntimeException('Make Cluster Key error! The config of the cluster may be has something wrong!');
                    }
                    //init nodes
                    $this->initNodes($clusterHashKey, $currentClusterConfig);
                    //init virtual nodes
                    $virtualNodeCount = !empty( $currentClusterConfig['weight'] ) ? $currentClusterConfig['weight'] * $this->baseNodeNum : $this->baseNodeNum;
                    for ($i = 0; $i < $virtualNodeCount; $i++) {
                        $iNodeKey = Hashing::hashToNumberic($clusterHashKey . '_' . $i, HashMode::NUM_CRC32);
                        $this->clusterHashNodes[$iNodeKey] = $clusterHashKey;
                    }
                    $arrConfigInfo = null;
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
        //set the zero key with the value of this first of  $this->clusterHashNodes
        $this->clusterHashNodes[0] = array_slice($this->clusterHashNodes, 0, 1)[0];
        //ksort
        ksort($this->clusterHashNodes);

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
            $return = Hashing::hashToStr(http_build_query($config), HashMode::STR_SHA384);
        }

        return $return;
    }

    /**
     * Init distribution nodes
     *
     *
     * @param string $clusterHashKey
     * @param array  $currentClusterConfig
     *
     * @return $this
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

}