<?php

namespace Doxport\Pass;

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

    public function __construct(Vertices $vertices, Driver $driver, Delete $action)
    {
        $this->vertices = $vertices;
        $this->graph = $vertices->getVertexFirst()->getGraph();
        $this->action = $action;
    }

    public function run()
    {
        foreach ($this->vertices as $vertex) {
            $shortestPath = new BreadthFirst($vertex);
            $walk = $shortestPath->getWalkTo($this->vertices->getVertexLast());

            foreach ($walk->getEdges() as $edge) {
                $this->action->addJoin($edge);
            }
        }
    }

}
