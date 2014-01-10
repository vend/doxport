<?php

namespace Doxport;

use Doxport\Metadata\Driver;
use Doxport\Metadata\Entity;
use Fhaculty\Graph\Algorithm\Search\BreadthFirst;
use Fhaculty\Graph\Algorithm\TopologicalSort;
use Fhaculty\Graph\Exporter\Dot;
use Fhaculty\Graph\Exporter\Image;
use Fhaculty\Graph\Graph;

class EntityGraph
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @param string $root
     */
    public function __construct($root)
    {
        $this->root   = $root;
        $this->graph  = new Graph();
    }

    /**
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
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
            $this->graph->createVertex($entity);
        }

        foreach ($entities as $entity) {
            foreach ($driver->getEntityMetadata($entity)->getClassMetadata()->getAssociationMappings() as $association) {
                if (!$associationFilter($association)) {
                    continue;
                }

                $edge = $this->graph->getVertex($association['sourceEntity'])->createEdgeTo(
                    $this->graph->getVertex($association['targetEntity'])
                );
                $edge->setLayoutAttribute('label', $association['fieldName']);
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
     * @return string
     */
    public function __toString()
    {
        $exporter = new Dot();
        $this->graph->setExporter($exporter);
        return (string)$this->graph;
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
