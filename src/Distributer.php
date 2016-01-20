<?php

namespace Leaf\Distributer;

use Leaf\Distributer\DistriHandler\ConsistentHashing;
use Leaf\Distributer\DistriHandler\DistriAbstract;
use Leaf\Distributer\DistriHandler\Modulo;

/**
 * Class Distributer
 * 分布式分发器
 *
 * @package Leaf\Distributer
 */
class Distributer
{

    /**
     * 当前分布式分发器所用的分布式算法类型
     *
     * @var int
     */
    protected $distriMode = DistriMode::DIS_CONSISTENT_HASHING;

    /**
     * 当前分布式分发器的分布式算法处理器
     *
     * @var DistriAbstract
     */
    protected $distriHandler = null;


    /**
     * 初始化分布式分发器
     * 根据分布式算法类型，获取分布式算法的处理器handler。然后根据配置文件，调用分布式算法处理器，做分布式处理的初始化工作
     *
     * @param array $clusterConfig 分布式集群的配置。你可以配置一个集群下的多个组，每个组都是由若干个主从对组成的
     *
     * @return Distributer|Modulo|DistriAbstract
     * @throws \Exception
     */
    public function init(array $clusterConfig = [])
    {
        $distriHandler = null;
        try {
            //根据分布式算法类型，初始化分布式算法处理器
            switch ($this->distriMode) {
                case DistriMode::DIS_CONSISTENT_HASHING:
                    $distriHandler = new ConsistentHashing();
                    break;
                case DistriMode::DIS_MODULO:
                    $distriHandler = new Modulo();
                    break;
            }
            //如果分布式算法处理器合法，则根据配置，做分布式算法处理的初始化工作
            if (is_object($distriHandler) && ( is_subclass_of($distriHandler, DistriAbstract::class) )) {
                //初始化分布式算法处理器
                $distriHandler->init($clusterConfig);
                //将初始化后的分布式算法处理器，添加到当前分布式分发器的handler属性中
                $this->setDistriHandler($distriHandler);
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * 获取当前分布式分发器的分布式算法类型
     *
     * @return int 它的类型如：DistriMode::DIS_CONSISTENT_HASHING 或 DistriMode::Modulo 等
     */
    public function getDistriMode()
    {
        return $this->distriMode;
    }

    /**
     * 设置分布式算法类型
     *
     * @param int $mode 它的类型如：DistriMode::DIS_CONSISTENT_HASHING 或 DistriMode::Modulo 等，如果你自写了分布式算法并做了注册，则可能为
     *                  你定义的值
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
     * 获取当前分布式分发器的分布式算法处理器
     *
     * @return ConsistentHashing|Modulo|DistriAbstract
     */
    protected function getDistriHandler()
    {
        return $this->distriHandler;
    }

    /**
     * 为当前的分布式分发器设置分布式算法处理器
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
     * 根据提供的要查找的key，获取与这个key想对应的一个cluster集群配置中的一个具体配置
     * 实际上，根据参数$key先获取的cluster集群中的某一组配置标识，这一组中既包含master写配置，又包含slave读配置。但是，具体是取读配置
     * 还是写配置，你是可以自由选择的。
     *
     * @param string $key  要查找配置的key
     * @param string $from 从什么类型的服务器中获取配置
     *
     * @return array
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