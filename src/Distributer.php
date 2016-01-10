<?php

namespace Leaf\Distribution;

use Leaf\Distribution\DistriHandler\ConsistentHashing;
use Leaf\Distribution\DistriHandler\DistriAbstract;
use Leaf\Distribution\DistriHandler\Modulo;

/**
 * Class Distributer
 * 实现原理：①：模拟哈希环，②：在哈希换上，根据数组配置，落上很多虚拟节点，③：将key转换为整型，寻找合适的落点
 *
 * @package Leaf\Consistent\Hashing
 */
class Distributer
{

    /**
     * distribution mode
     *
     * @var int
     */
    protected $distriMode = DistriMode::DIS_CONSISTENT_HASHING;

    /**
     * distribution handler
     *
     * @var null
     */
    protected $distriHandler = null;

    /**
     *
     * @return DistriAbstract
     */
    public function instanceHandler()
    {
        $distriHandler = null;
        switch ($this->distriMode) {
            case DistriMode::DIS_CONSISTENT_HASHING:
                $distriHandler = new ConsistentHashing();
                break;
            case DistriMode::DIS_MODULO:
                $distriHandler = new Modulo();
                break;
        }
        if ( !is_object($distriHandler) && $distriHandler instanceof DistriAbstract) {
            $this->setDistriHandler($distriHandler);
        }

        return $distriHandler;
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
        if (in_array($mode, DistriMode::$arrDistriMode)) {
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

    public function setConfig($arrConfigGroup = [])
    {
        $this->getDistriHandler()->setConfig($arrConfigGroup);

        return $this;
    }

}