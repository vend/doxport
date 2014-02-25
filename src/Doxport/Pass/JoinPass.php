<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
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
     * @var Action
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
     * @return void
     */
    public function run()
    {
        $this->graph->from($this->driver, function (array $association) {
            // Ignore self-joins
            return
                $this->driver->isSupportedAssociation($association)
                && $this->driver->isCoveredAssociation($association)
                && $this->driver->isColumnOwnerAssociation($association)
                && $this->driver->isConstraintAssociation($association)
                && !$this->driver->isOptionalAssociation($association);
        });

        $this->graph->filterConnected();

        foreach ($this->vertices as $vertex) {
            /* @var $vertex Vertex */
            if ($vertex->getId() == $this->graph->getRoot()) {
                if (!$this->includeRoot) {
                    continue;
                } else {
                    $walk = Walk::factoryFromEdges([], $vertex);
                }
            } else {
                $shortestPath = new BreadthFirst($vertex);
                $walk = $shortestPath->getWalkTo($this->vertices->getVertexLast());
            }

            // Process self-joins on the target first
            $selfJoins = $this->driver->getEntityMetadata($vertex->getId())
                ->getClassMetadata()->getAssociationsByTargetClass($vertex->getId());

            foreach ($selfJoins as $association) {
                $this->action->processSelfJoin($walk, $association);
            }

            // Then the actual target
            $this->action->process($walk);
        }
    }

}
