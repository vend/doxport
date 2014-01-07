<?php

namespace Doxport\Pass;

use Doxport\EntityGraph;
use Doxport\Action\Delete;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Algorithm\ShortestPath\BreadthFirst;
use Fhaculty\Graph\Set\Vertices;

class JoinPass extends Pass
{
    /**
     * @var \Fhaculty\Graph\Set\Vertices
     */
    protected $vertices;

    /**
     * @var \Fhaculty\Graph\Graph
     */
    protected $graph;

    protected $action;

    public function __construct(Vertices $vertices, Driver $driver, EntityGraph $graph, Delete $action)
    {
        $this->vertices = $vertices;
        $this->graph = $graph;
        $this->action = $action;
        $this->driver = $driver;
    }

    public function run()
    {
        $this->graph->from($this->driver, function (array $association) {
            return
                $this->driver->isSupportedAssociation($association)
                && $this->driver->isCoveredAssociation($association)
                && $this->driver->isConstraintAssociation($association)
                && !$this->driver->isOptionalAssociation($association);
        });

        $this->graph->connectedTo($this->vertices->getVertexLast()->getId());

        foreach ($this->vertices as $vertex) {
            $shortestPath = new BreadthFirst($vertex);
            $walk = $shortestPath->getWalkTo($this->vertices->getVertexFirst());

            foreach ($walk->getEdges() as $edge) {
                $this->action->addJoin($edge);
            }
        }
    }

}
