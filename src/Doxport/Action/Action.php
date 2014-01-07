<?php

namespace Doxport\Action;

use Doctrine\ORM\EntityManager;
use Doxport\Criteria;
use Doxport\Schema;
use Doxport\Util\SimpleObjectSerializer;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var OutputInterface
     */
    protected $output;

    /**
     * File descriptor to output file
     *
     * @var resource
     */
    protected $file;

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
     * @param OutputInterface $output
     * @return void
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @todo better name, not really serialization
     * @param $entity
     * @return array
     */
    protected function serialize($entity)
    {
        if (method_exists($entity, '__sleep')) {
            return $entity->__sleep();
        }

        $serializer = new SimpleObjectSerializer($this->em);
        return $serializer->serialize($entity);
    }
}