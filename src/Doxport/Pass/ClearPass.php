<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\EntityGraph;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;

class ClearPass extends Pass
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
        foreach ($this->vertices as $vertex) {
            /* @var $vertex Vertex */
            if ($vertex->getId() == $this->graph->getRoot()) {
                if (!$this->includeRoot) {
                    continue;
                }
            }

            $entity = $vertex->getId();
            $metadata = $this->driver->getEntityMetadata($entity);

            foreach ($metadata->getProperties() as $property) {
                $b = 2;
            }

            // Find clear annotations on $entity
            // Collect them all, call action
        }
    }

}
