<?php

namespace Doxport\Pass;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;

class ConstraintPass extends Pass
{
    protected $driver;

    protected $root;

    public function __construct(Driver $driver, EntityGraph $graph, $root)
    {
        $this->driver = $driver;
        $this->root = $root;
        $this->graph = $graph;
    }

    /**
     * @return \Fhaculty\Graph\Set\Vertices In work order to respect constraints
     */
    public function run()
    {
        $this->graph->from($this->driver, function (array $association) {
            return
                $this->driver->isSupportedAssociation($association)
                && $this->driver->isCoveredAssociation($association)
                && $this->driver->isConstraintAssociation($association)
                && !$this->driver->isOptionalAssociation($association);
        });

        $this->graph->connectedTo($this->root);

        return $this->graph->topologicalSort();
    }
}
