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

    /**
     * The name of the root criteria
     *
     * @var string
     */
    protected $root;

    /**
     * @var Metadata\Driver
     */
    protected $driver;

    /**
     * Constructor
     *
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

        foreach ($this->driver->getEntityNames() as $name) {
            $this->unjoined[$name] = $name;
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
     * @return Criteria
     */
    public function getRootCriteria()
    {
        return $this->getCriteria($this->root);
    }

    /**
     * @param string  $name
     * @param integer $level
     * @return Criteria
     * @throws \InvalidArgumentException On an invalid level parameter
     */
    public function markJoined($name, $level)
    {
        if (!is_numeric($level)) {
            throw new \InvalidArgumentException('Level must be numeric');
        }

        if (!isset($this->joined[$level])) {
            $this->joined[$level] = [];
        }

        unset($this->unjoined[$name]);
        return $this->joined[$level][$name] = $this->getCriteria($name);
    }

    /**
     * @param string $name The name of the criteria object to retrieve
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

    /**
     * @param string $criteria
     * @param string $target
     * @todo Check there is an association between the entities
     * @todo Check it is covered by an index
     * @return false|array Association mapping if there is one that can be used
     */
    public function canBeLinked($criteria, $target)
    {
        $criteria = $this->getCriteria($criteria);
        $target   = $this->getCriteria($target);

        $possible = $criteria->getAssociationsTo($target->getEntityName());

        if (!$possible) {
            return false;
        }

        foreach ($possible as $association) {
            if (!$association['isOwningSide']) {
                continue;
            }

            if ($this->isOptionalAssociation($association)) {
                continue;
            }

            if (!$this->isCoveredAssociation($association)) {
                continue;
            }

            return $association;
        }

        return false;
    }

    protected function isCoveredAssociation($association)
    {
        return $this->driver->isCovered($association['sourceEntity'], $association['joinColumnFieldNames']);
    }

    /**
     * @param array $association
     * @return boolean
     */
    protected function isOptionalAssociation(array $association)
    {
        if ($association['joinColumns']) {
            foreach ($association['joinColumns'] as $joinColumn) {
                if (isset($joinColumn['nullable']) && !$joinColumn['nullable']) {
                    // nullable is true by default
                    return false;
                }
            }
        }

        return true;  //$this->driver->isNullableColumn($association['sourceEntity'], $association['joinColumnFieldNames']);
    }

    /**
     * @param string $criteria
     * @param string $target
     * @param array $via
     * @return void
     */
    public function link($criteria, $target, array $via)
    {
        $criteria = $this->getCriteria($criteria);
        $target   = $this->getCriteria($target);

        $criteria->attachChild($target, $via);
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        $string = 'Schema: joined...' . "\n";
        $string .= (string)$this->criteria[$this->root];
        $string .= "\n";

        $string .= 'Schema: unjoined...' . "\n";

        foreach ($this->unjoined as $name) {
            $string .= (string)$this->criteria[$name];
        }

        $string .= "\n";

        $string .= 'End of schema';
        $string .= "\n";

        return $string;
    }
}
