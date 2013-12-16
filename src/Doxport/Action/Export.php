<?php

namespace Doxport\Action;

use Doctrine\ORM\EntityManager;
use Doxport\Criteria;
use Doxport\Schema;

class Export extends Action
{
    protected $schema;

    protected $em;

    public function __construct(EntityManager $em, Schema $schema)
    {
        $this->em = $em;
        $this->schema = $schema;
    }

    public function run()
    {
        $this->walk($this->schema->getRootCriteria());
    }

    /**
     * @param Criteria $criteria
     */
    protected function walk(Criteria $criteria)
    {
        // Recurse for children
        foreach ($criteria->getChildren() as $child) {
            $this->walk($child);
        }

        $qb = $this->em->createQueryBuilder();

        $qb->from($criteria->getEntityName(), 'c');

        if (($parent = $criteria->getParent())) {
            $a = $parent;

            // Collect criteria/join to parent
        }


    }
}
