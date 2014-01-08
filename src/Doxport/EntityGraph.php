<?php

namespace Doxport;

use Doxport\Metadata\Driver;
use Doxport\Metadata\Entity;
use Fhaculty\Graph\Algorithm\Search\BreadthFirst;
use Fhaculty\Graph\Algorithm\TopologicalSort;
use Fhaculty\Graph\Exporter\Image;
use Fhaculty\Graph\Graph;

class EntityGraph
{
    /**
     * @var Entity[]
     */
    protected $entities = [];

    /**
     * @var string
     */
    protected $root;

    /**
     * @param string $root
     */
    public function __construct($root)
    {
        $this->root   = $root;
        $this->graph  = new Graph();
    }

    /**
     * @param Driver   $driver
     * @param callable $associationFilter
     * @return void
     */
    public function from(Driver $driver, callable $associationFilter)
    {
        $entities = $driver->getEntityNames();

        foreach ($entities as $entity) {
            $this->entities[$entity] = $driver->getEntityMetadata($entity);
            $this->graph->createVertex($entity);
        }

        foreach ($this->entities as $entity) {
            foreach ($entity->getClassMetadata()->getAssociationMappings() as $association) {
                if (!$associationFilter($association)) {
                    continue;
                }

                $source = $this->graph->getVertex($association['sourceEntity']);
                $target = $this->graph->getVertex($association['targetEntity']);
                $via    = $association['fieldName'];

                $edge = $source->createEdgeTo($target);
                $edge->setLayoutAttribute('label', $via);
            }
        }
    }

    /**
     * Filters the graph so that only entities connected to the root are left
     *
     * Should be run after you've added some set of associations with from()
     *
     * @return void
     */
    public function filterConnected()
    {
        $alg = new BreadthFirst($this->graph->getVertex($this->root));
        $alg->setDirection(BreadthFirst::DIRECTION_REVERSE);
        $vertices = $alg->getVertices();

        $this->graph = $this->graph->createGraphCloneVertices($vertices);
    }

    /**
     * Exports an image of the graph to the given path
     *
     * @param string $path
     * @return void
     */
    public function export($path)
    {
        $exporter = new Image();
        $this->graph->setExporter($exporter);

        file_put_contents($path, (string)$this->graph);
    }

    /**
     * Performs a topological sort
     *
     * @return \Fhaculty\Graph\Set\Vertices
     */
    public function topologicalSort()
    {
        $sort = new TopologicalSort($this->graph);
        return $sort->getVertices();
    }

    /**
     * The root entity vertex ID
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
}
