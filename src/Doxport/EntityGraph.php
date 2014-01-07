<?php

namespace Doxport;

use Doxport\Metadata\Driver;
use Doxport\Metadata\Entity;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;

class EntityGraph
{
    protected $driver;

    /**
     * @var Entity[]
     */
    protected $entities = [];

    /**
     * @param Driver $driver
     */
    public function __construct($driver = null)
    {
        $this->driver = $driver;
        $this->graph  = new Graph();
    }

    public function from(Driver $driver)
    {
        $entities = $driver->getEntityNames();

        foreach ($entities as $entity) {
            $this->entities[$entity] = new Entity($driver->getEntityMetadata($entity));
            $this->graph->createVertex($entity);
        }

        foreach ($this->entities as $entity) {
            foreach ($entity->getClassMetadata()->getAssociationMappings() as $association) {
                $a = 1;
            }
        }
    }
}
