<?php

namespace Leaf\Distributer;

/**
 * Class DistriManager
 *
 * 分布式分发器器管理器
 * 你的项目中，可能有多于一个分布式分发器。这个时候你就需要这个manager来做管理了。例如：你的项目中有2个redis大集群。第一个集群
 * 有一致性哈希做分布式，另外一个大群用取模算法做分布式。详情 READEME.MD
 *
 * @package Leaf\Distributer
 */
class DistriManager
{

    public $clonedDistributer = null;

    /**
     * 获取一个分布式分配器
     *
     * @param string $name  分布式分发器名。
     *                      默认情况下，你可以不设置此参数，系统会用默认值“default”来做为默认的分布式分发器的名称。但是，如果你在
     *                      项目规划前期，就已经预料到你将来的项目可能会用到多个集群的情况。每个集群处理一块儿业务，每个集群是多个
     *                      redis实例组成。那么，推荐你一开始就设置此参数。
     *
     * @param int    $mode  分布式分发器所用的分布式算法，默认为一致性哈希分布式算法
     *
     * @return Distributer
     */
    public function getDistributer($name = 'default', $mode = DistriMode::DIS_CONSISTENT_HASHING)
    {
        $distributer = null;
        if ( !empty( $name ) && is_string($name)) {
            //如果分发器存在，则直接返回
            if (isset( $this->$name )) {
                $distributer = $this->$name;
            }
            //如果分发器不存在，则获取一个分发器实例
            else {
                $distributer = $this->getAClonedDistributer();
                $distributer->setDistriMode($mode);
                $this->$name = $distributer;
            }
        }
        else {
            throw new \InvalidArgumentException("get Distributer error, unvaliad param!");
        }

        return $distributer;
    }

    /**
     * 获取一个分布式分发器的克隆对象
     * 应用PHP设计模式之一的克隆模式。克隆模式不需要直接实例化对象，在某种情况上来看，克隆模式由于是直接做内存拷贝，效率是高
     * 过直接实例化对象然后做各种属性赋值操作的
     *
     * @return Distributer
     */
    protected function getAClonedDistributer()
    {
        if ( !isset( $this->clonedDistributer )) {
            $this->clonedDistributer = new Distributer();
        }

        return clone $this->clonedDistributer;
    }

}