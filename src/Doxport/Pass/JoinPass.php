<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\Action\Base\QueryAction;
use Doxport\EntityGraph;
use Doxport\Exception\Exception;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Algorithm\ShortestPath\BreadthFirst;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;

class JoinPass extends Pass
{
    /**
     * @var QueryAction
     */
    protected $action;

    /**
     * @var boolean
     */
    protected $includeRoot = true;

    /**
     * @param Driver $driver
     * @param EntityGraph $graph
     * @param Action $action
     * @param Vertices $vertices
     */
    public function __construct(Driver $driver, EntityGraph $graph, Action $action, Vertices $vertices = null)
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
     * @throws Exception
     */
    public function run()
    {
        parent::run();

        if (!isset($this->vertices)) {
            throw new Exception('Cannot run join pass without a set of vertices to run on');
        }

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
