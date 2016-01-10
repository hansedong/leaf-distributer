<?php

namespace Leaf\Distribution;

class DistriManager
{

    public $clonedDistributer = null;

    /**
     * get a distributer according to the param $name
     *
     * @param string $name
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
                $distributer->setDistriMode(DistriMode::DIS_CONSISTENT_HASHING);
                $this->$name = $distributer;
            }
        }

        return $distributer;
    }

    /**
     * get a cloned distributer
     * use clone pattern (one of design patterns of PHP) to decrease performance overhead
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