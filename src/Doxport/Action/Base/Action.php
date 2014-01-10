<?php

namespace Doxport\Action\Base;

use Doxport\Schema;
use Doxport\Util\SimpleObjectSerializer;
use Fhaculty\Graph\Walk;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param Walk   $fromTargetToRoot
     * @return void
     */
    abstract public function process(Walk $fromTargetToRoot);

    /**
     * @param Walk  $fromTargetToRoot
     * @param array $associationToAndFromTarget
     * @return void
     */
    abstract public function processSelfJoin(Walk $fromTargetToRoot, array $associationToAndFromTarget);

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

    /**
     * @param string $class
     * @return mixed
     */
    protected function getClassName($class)
    {
        $parts = explode('\\', $class);
        return $parts[count($parts) - 1];
    }
}
