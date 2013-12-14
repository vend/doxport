<?php

namespace Doxport;

use Doxport\Metadata\Driver;

class Schema
{
    /**
     * @var array<string => Criteria>
     */
    protected $criteria = [];

    /**
     * @var array<string>
     */
    protected $unjoined = [];

    /**
     * @var array<int><string => Criteria>
     */
    protected $joined = [];

    protected $root;

    /**
     * @var Metadata\Driver
     */
    protected $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

        foreach ($this->driver->getEntityNames() as $name) {
            $this->getCriteria($name);
            $this->unjoined[] = $name;
        }
    }

    /**
     * @param string $name
     * @return Criteria
     */
    public function setRoot($name)
    {
        $this->root = $name;
        return $this->markJoined($name, 0);
    }

    /**
     * @param string $name
     * @param int $level
     * @return Criteria
     * @throws \InvalidArgumentException
     */
    public function markJoined($name, $level)
    {
        if (!is_numeric($level)) {
            throw new \InvalidArgumentException('Level must be numeric');
        }

        if (!isset($this->joined[$level])) {
            $this->joined[$level] = [];
        }

        return $this->joined[$level][$name] = $this->getCriteria($name);
    }

    /**
     * @param string $name
     * @return Criteria
     */
    public function getCriteria($name)
    {
        if (!isset($this->criteria[$name])) {
            $criteria = new Criteria();
            $criteria->setEntity($this->driver->getEntityMetadata($name));

            $this->criteria[$name] = $criteria;
        }

        return $this->criteria[$name];
    }

    /**
     * @return array[Criteria[]]
     */
    public function getAllJoinedCriteria()
    {
        return $this->joined;
    }

    /**
     * @return Criteria[]
     */
    public function getAllUnjoinedCriteria()
    {
        return $this->unjoined;
    }

    // TODO Check there is an association between the entities
    // TODO Check it is covered by an index
    public function canBeLinked($criteria, $target)
    {
        $criteria = $this->getCriteria($criteria);
        $target   = $this->getCriteria($target);

        $possible = $criteria->getAssociationsTo($target);

        return false;
    }

    /**
     * @param $criteria
     * @param $target
     * @param $via
     */
    public function link($criteria, $target, $via)
    {
        $criteria = $this->getCriteria($criteria);
        $target   = $this->getCriteria($target);

        $criteria->attachChild($target, $via);
    }

    public function __toString()
    {
        $string = 'Schema: joined...';
        $string .= (string)$this->criteria[$this->root];
        $string .= 'Schema: unjoined...';
        foreach ($this->unjoined as $name) {
            $string .= (string)$this->criteria[$name];
        }
        $string .= 'End schema';
        return $string;
    }
}
