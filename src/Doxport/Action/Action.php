<?php

namespace Doxport\Action;

use Doctrine\ORM\EntityManager;
use Doxport\Criteria;
use Doxport\Schema;
use Doxport\Util\SimpleObjectSerializer;

abstract class Action
{
    const TYPE_DFS = 0;
    const TYPE_BFS = 1;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param Criteria $criteria
     * @return void
     */
    abstract protected function process(Criteria $criteria);

    /**
     * @return integer See Action::TYPE_*
     */
    abstract protected function getType();

    /**
     * @param EntityManager $em
     * @param Schema        $schema
     */
    public function __construct(EntityManager $em, Schema $schema)
    {
        $this->em = $em;
        $this->schema = $schema;

        $this->configure();
    }

    /**
     * Configures the action
     */
    protected function configure()
    {
    }

    /**
     * Runs the action
     *
     * @return void
     */
    public function run()
    {
        $root = $this->schema->getRootCriteria();

        if ($this->getType() == self::TYPE_DFS) {
            $this->dfs($root);
        } elseif ($this->getType() == self::TYPE_BFS) {
            $this->bfs($root);
        }
    }

    /**
     * Does a depth first search
     *
     * @param Criteria $criteria
     * @return void
     */
    protected function dfs(Criteria $criteria)
    {
        foreach ($criteria->getChildren() as $child) {
            $this->dfs($child);
        }

        $this->process($criteria);
    }

    /**
     * Does a breadth first search
     *
     * @param Criteria $criteria
     * @return void
     */
    protected function bfs(Criteria $criteria)
    {
        $this->process($criteria);

        foreach ($criteria->getChildren() as $child) {
            $this->bfs($child);
        }
    }

    protected function serialize($entity)
    {
        if (method_exists($entity, '__sleep')) {
            return $entity->__sleep();
        }

        $serializer = new SimpleObjectSerializer($this->em);
        return $serializer->serialize($entity);
    }
}
