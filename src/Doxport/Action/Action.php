<?php

namespace Doxport\Action;

use Doxport\Schema;
use Doxport\Util\SimpleObjectSerializer;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Action
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param OutputInterface $output
     * @return void
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param \Fhaculty\Graph\Vertex $target
     * @param \Fhaculty\Graph\Walk   $walkToRoot
     * @return void
     */
    abstract public function process(Vertex $target, Walk $walkToRoot);

    /**
     * @param object $entity
     * @todo better name, not really serialization
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
