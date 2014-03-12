<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\QueryAction;
use Doxport\EntityGraph;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Algorithm\ShortestPath\BreadthFirst;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;

class JoinPass extends Pass
{
    /**
     * @var Vertices
     */
    protected $vertices;

    /**
     * @var QueryAction
     */
    protected $action;

    /**
     * @var boolean
     */
    protected $includeRoot = true;

    /**
     * @param Driver      $driver
     * @param EntityGraph $graph
     * @param Vertices    $vertices
     * @param Action      $action
     */
    public function __construct(Driver $driver, EntityGraph $graph, Vertices $vertices, Action $action)
    {
        parent::__construct($driver, $graph, $action);

        $this->vertices = $vertices;
    }

    /**
     * @inheritDoc
     */
    protected function configureGraph()
    {
        $this->graph->from($this->driver, function (array $association) {
            return
                $this->driver->isSupportedAssociation($association)
                && $this->driver->isCoveredAssociation($association)
                && $this->driver->isColumnOwnerAssociation($association)
                && $this->driver->isConstraintAssociation($association)
                && !$this->driver->isOptionalAssociation($association);
        });

        $this->graph->filterConnected();
    }

    /**
     * @return void
     */
    public function run()
    {
        parent::run();

        foreach ($this->vertices as $vertex) {
            /* @var $vertex Vertex */
            if ($vertex->getId() == $this->graph->getRoot()) {
                if (!$this->includeRoot) {
                    continue;
                }
            }

            $walk = $this->getWalkForVertex($vertex);
            $this->action->process($walk);
        }
    }

    /**
     * @param Vertex $vertex
     * @return Walk
     */
    protected function getWalkForVertex(Vertex $vertex)
    {
        if ($vertex->getId() == $this->graph->getRoot()) {
            return Walk::factoryFromEdges([], $vertex);
        } else {
            $shortestPath = new BreadthFirst($vertex);
            return $shortestPath->getWalkTo($this->vertices->getVertexLast());
        }
    }
}
