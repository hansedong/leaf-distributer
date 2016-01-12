<?php

namespace Leaf\Distribution;

/**
 * Class DistriManager
 * The manager of distributers
 * If you hanve more than one clusters in your project, you may need to manage the distributers, this class is to help
 * you to manage them in a bright way.
 *
 * @package Leaf\Distribution
 */
class DistriManager
{

    public $clonedDistributer = null;

    /**
     * 获取一个分布式分配器
     *
     * @param string $name Distributer name. You needn't to set this param if your project use one cluster only, but if
     *                     you can foresee that you will have more than one clusters in your project. You are
     *                     recommended to set this distributer name now to make a distinction between the clusters.
     * @param int    $mode The distribution type of the distributer. DistriMode::DIS_CONSISTENT_HASHING as consistent
     *                     hashing algorithm(default value of this param) and DistriMode::DIS_MODULO as modules
     *                     algorithm.
     *
     * @return Distributer
     */
    public function getDistributer($name = 'default', $mode = DistriMode::DIS_CONSISTENT_HASHING)
    {
        $distributer = null;
        if ( !empty( $name ) && is_string($name)) {
            if (isset( $this->$name )) {
                $distributer = $this->$name;
            }
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
     * Get a cloned object of the distributer
     * Clone pattern, is one of design patterns of PHP，is can decrease the overhead of the system in a way.
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