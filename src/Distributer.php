<?php

namespace Leaf\Distributer;

use Leaf\Distributer\DistriHandler\ConsistentHashing;
use Leaf\Distributer\DistriHandler\DistriAbstract;
use Leaf\Distributer\DistriHandler\Modulo;

/**
 * Class Distributer
 * Distribution distributer
 *
 * @package Leaf\Distributer
 */
class Distributer
{

    /**
     * The type of the distribution algorithm
     *
     * @var int
     */
    protected $distriMode = DistriMode::DIS_CONSISTENT_HASHING;

    /**
     * The distribution handler of this distributer
     *
     * @var DistriAbstract
     */
    protected $distriHandler = null;


    /**
     * Instance the distribution handler
     * The handler could be consistent-hashing algorithm handler or modulo algorithm handler or a handler that customed
     * by yourself.
     *
     * @param array $clusterConfig  The configure group. You can config more than one servers group. Please see the
     *                              configure example in Example/standard-config.php
     *
     * @return DistriAbstract
     * @throws \Exception
     */
    public function init(array $clusterConfig = [])
    {
        ini_set('display_errors', 1);
        $distriHandler = null;
        try {
            //instance a distribution handler
            switch ($this->distriMode) {
                case DistriMode::DIS_CONSISTENT_HASHING:
                    $distriHandler = new ConsistentHashing();
                    break;
                case DistriMode::DIS_MODULO:
                    $distriHandler = new Modulo();
                    break;
            }
            if (is_object($distriHandler) && ( is_subclass_of($distriHandler, DistriAbstract::class) )) {
                //init the handler
                $distriHandler->init($clusterConfig);
                //set the handler of this distributer
                $this->setDistriHandler($distriHandler);
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Get the distribution mode
     *
     * @return int it can be one DistriMode::DIS_CONSISTENT_HASHING or DistriMode::Modulo etc
     */
    public function getDistriMode()
    {
        return $this->distriMode;
    }

    /**
     * set the distribution mode
     *
     * @param int $mode it can be one DistriMode::DIS_CONSISTENT_HASHING or DistriMode::Modulo etc
     */
    public function setDistriMode($mode = DistriMode::DIS_CONSISTENT_HASHING)
    {
        if (array_key_exists($mode, DistriMode::$arrDistriModeClass)) {
            $this->distriMode = $mode;
        }
        else {
            throw new \InvalidArgumentException('Invalid mode of distribution，the param can be DistriMode::DIS_CONSISTENT_HASHING or DistriMode::Modulo!');
        }
    }

    /**
     * Get the distribution handler of this manager
     *
     * @return ConsistentHashing|Modulo|DistriAbstract
     */
    protected function getDistriHandler()
    {
        return $this->distriHandler;
    }

    /**
     * Set the distribution handler of this manager
     *
     * @param DistriAbstract $handler
     *
     * @return $this
     */
    protected function setDistriHandler(DistriAbstract $handler)
    {
        if ($handler instanceof DistriAbStract) {
            $this->distriHandler = $handler;
        }
        else {
            throw new \InvalidArgumentException('set handler error! it must be a instance of DistriAbstract');
        }

        return $this;
    }

    /**
     * Init the distribution handler
     *
     * @return Distributer
     */
    public function initDistributionHandler()
    {
        if ($distributer = $this->getDistriHandler()) {
            $distributer->init();
        }
        else {
            throw new \RuntimeException('the distribution handler has not been setted yet!');
        }

        return $this;
    }

    /**
     * Find the config of a server node according to the param $key recevied
     *
     * @return Distributer
     */
    public function lookUp($key, $from = 'read')
    {
        $config = [];
        if ( !is_string($key) || empty( $key )) {
            throw new \InvalidArgumentException('param error, empty param!');
        }
        if ($distributer = $this->getDistriHandler()) {
            $config = $distributer->lookUp($key, $from);
        }
        else {
            throw new \RuntimeException('the distribution handler has not been setted yet!');
        }

        return $config;
    }

}