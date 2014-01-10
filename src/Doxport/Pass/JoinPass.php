<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\EntityGraph;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Algorithm\ShortestPath\BreadthFirst;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;

class JoinPass extends Pass
{
    /**
     * @var \Fhaculty\Graph\Set\Vertices
     */
    protected $vertices;

    /**
     * @var \Doxport\Action\Action
     */
    protected $action;

    /**
     * @param Driver      $driver
     * @param EntityGraph $graph
     * @param Vertices    $vertices
     * @param Action      $action
     */
    public function __construct(Driver $driver, EntityGraph $graph, Vertices $vertices, Action $action)
    {
        parent::__construct($driver, $graph);

        $this->vertices = $vertices;
        $this->action = $action;
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
                && $this->driver->isConstraintAssociation($association)
                && !$this->driver->isOptionalAssociation($association);
        });

        $this->graph->filterConnected();

        foreach ($this->vertices as $vertex) {
            /* @var $vertex Vertex */
            if ($vertex->getId() == $this->graph->getRoot()) {
                continue;
            }

            $shortestPath = new BreadthFirst($target = $vertex);
            $walk = $shortestPath->getWalkTo($root = $this->vertices->getVertexLast());

            // Process self-joins on the target first
            $selfJoins = $this->driver->getEntityMetadata($target->getId())
                ->getClassMetadata()->getAssociationsByTargetClass($target->getId());

            foreach ($selfJoins as $association) {
                $this->action->processSelfJoin($walk, $association);
            }

            // Then the actual target
            $this->action->process($walk);
        }
    }

}
