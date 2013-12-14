<?php

namespace Doxport\Metadata;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use LogicException;

class Driver
{
    protected $em;
    protected $entities = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getEntityNames()
    {
        return $this->getDoctrineMetadataDriver()->getAllClassNames();
    }

    protected function getDoctrineMetadataDriver()
    {
        $driver = $this->em->getConfiguration()->getMetadataDriverImpl();

        if (!($driver instanceof AnnotationDriver)) {
            throw new LogicException('Expects Doctrine2 to be using an annotation metadata driver');
        }

        return $driver;
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     * @throws LogicException
     */
    protected function getAnnotationReader()
    {
        return $this->getDoctrineMetadataDriver()->getReader();
    }

    /**
     * @param $entity
     * @return Entity
     */
    public function getEntityMetadata($entity)
    {
        if (isset($this->entities[$entity])) {
            return $this->entities[$entity];
        }

        $reader = $this->getAnnotationReader();
        $meta = new Entity($this->em->getClassMetadata($entity));

        foreach ($meta->getClassMetadata()->getReflectionProperties() as $name => $property) {
            $relevant = array_filter($reader->getPropertyAnnotations($property), function ($value) {
                return implode('\\', array_slice(explode('\\', get_class($value)), 0, -1)) == 'Doxport\\Annotation';
            });

            if (!$relevant) {
                continue;
            }

            $property = new Property($name, $relevant, $meta->getClassMetadata()->getAssociationMapping($property->name));
            $meta->addProperty($property);
        }

        return $this->entities[$entity] = $meta;
    }
}
