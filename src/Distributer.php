<?php

namespace Leaf\Distribution;

use Leaf\Distribution\DistriHandler\ConsistentHashing;
use Leaf\Distribution\DistriHandler\DistriAbstract;
use Leaf\Distribution\DistriHandler\Modulo;

/**
 * Class Distributer
 * Distribution distributer
 *
 * @package Leaf\Distribution
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
     * @param array $arrConfigGroup The configure group. You can config more than one servers group. Please see the
     *                              configure example in Example/standard-config.php
     *
     * @return DistriAbstract
     * @throws \Exception
     */
    public function instanceHandler(array $arrConfigGroup = [])
    {
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
            if ( !is_object($distriHandler) && $distriHandler instanceof DistriAbstract) {
                //set the configure of the distribution handler
                $distriHandler->setConfig($arrConfigGroup);
                //init the handler
                $distriHandler->init();
                //set the handler of this distributer
                $this->setDistriHandler($distriHandler);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * get the distribution mode
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
        if (in_array($mode, DistriMode::$arrDistriModeClass)) {
            $this->distriMode = $mode;
        }
        else {
            throw new \InvalidArgumentException('Invalid mode of distribution，the param can be DistriMode::DIS_CONSISTENT_HASHING or DistriMode::Modulo!');
        }
    }

    /**
     * get the distribution handler of this manager
     *
     * @return DistriAbstract
     */
    protected function getDistriHandler()
    {
        return $this->distriHandler;
    }

    /**
     * set the distribution handler of this manager
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
     * init the distribution handler
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
     * find the config of a server node according to the param $key recevied
     *
     * @return Distributer
     */
    public function lookUp($key)
    {
        if ( !is_string($key) || empty( $key )) {
            throw new \InvalidArgumentException('param error, empty param!');
        }
        if ($distributer = $this->getDistriHandler()) {
            $distributer->lookUp($key);
        }
        else {
            throw new \RuntimeException('the distribution handler has not been setted yet!');
        }

        return $this;
    }

}