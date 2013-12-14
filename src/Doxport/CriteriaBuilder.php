<?php

namespace Doxport;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;

class CriteriaBuilder
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Metadata\Driver
     */
    protected $driver;

    /**
     * @var Criteria
     */
    protected $target;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->driver = new Driver($this->em);
        $this->target = new Criteria();
    }

    // Fluent interface

    public function from($entity)
    {
        $this->target->setEntity($this->driver->getEntityMetadata($entity));
        return $this;
    }

    public function where($column, $value)
    {
        $this->target->addWhereEq($column, $value);
        return $this;
    }

    // Fluent interface end

    public function build()
    {
        $this->validate();
        return $this->doBuild();
    }

    // Public interface end

    protected function doBuild()
    {
        $this->follow($this->target);
        return $this->target;
    }

    protected function follow(Criteria $criteria)
    {
        foreach ($criteria->getPropertiesToFollow() as $property) {
            $new = new Criteria();
            $new->setEntity($this->driver->getEntityMetadata($property->getTargetEntity()));
            $new->setParent($criteria);

            $criteria->attachChild($new);
            $this->follow($new); // recurse
        }
    }

    protected function validate()
    {
        // TODO at least a where criteria (or id() has been called)
        // TODO the root class is selected
    }
}
