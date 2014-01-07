<?php

namespace Doxport\Action;

use Doxport\Schema;
use Doxport\Util\SimpleObjectSerializer;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param Vertex $target
     * @param Walk   $walkToRoot
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
