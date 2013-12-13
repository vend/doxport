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

    protected $root;
    protected $id;
    protected $type;

    protected $driver;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->driver = new Driver($this->em);
    }

    public function from($entity)
    {
        $this->root = $entity;
        return $this;
    }

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function build()
    {
        $this->validate();
        return $this->doBuild($this->root);
    }

    protected function doBuild()
    {
        $criteria = new Criteria($this->driver->getEntityMetadata($this->root));

        $this->follow($criteria);

        return $criteria;
    }

    protected function follow(Criteria $criteria)
    {
        foreach ($criteria->getPropertiesToFollow() as $property) {
            $new = new Criteria($this->driver->getEntityMetadata($property->getTargetEntity()));
            $new->setParent($criteria);

            $criteria->attach($new);
            $this->follow($new); // recurse
        }
    }

    protected function validate()
    {
        // TODO at least a where criteria (or id() has been called)
        // TODO the root class is selected
        $metadata = $this->driver->getEntityMetadata($this->root);
    }
}
