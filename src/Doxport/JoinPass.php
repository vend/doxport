<?php

namespace Doxport;

use Doxport\Metadata\Driver;

class JoinPass
{
    protected $driver;

    public function __construct(Driver $driver, Schema $schema)
    {
        $this->driver = $driver;
        $this->schema = $schema;
    }

    /**
     * Performs pass until the schema is no longer reduced
     */
    public function reduce()
    {
        $after = count($this->schema->getAllUnjoinedCriteria());

        do {
            $unjoined = $after;
            $this->pass();
            $after = count($this->schema->getAllUnjoinedCriteria());
        } while ($after < $unjoined);
    }

    /**
     * In this method:
     *   $criteria is the entity under consideration
     *   $target is a potential table, already joined, that it may be joined to
     */
    public function pass()
    {
        foreach ($this->schema->getAllUnjoinedCriteria() as $criteria) {
            foreach ($this->schema->getAllJoinedCriteria() as $level => $entities) { // In BFS order
                foreach ($entities as $name => $target) {
                    if (($via = $this->schema->canBeLinked($criteria, $target))) {
                        // If there's a covered relation between $criteria and $target
                        $this->schema->link($criteria, $target, $via);
                        $this->schema->markJoined($criteria->getEntityName(), $level + 1);
                        continue 2;
                    }
                }
            }
        }
    }
}
