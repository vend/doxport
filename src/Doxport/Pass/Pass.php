<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\EntityGraph;
use Doxport\File\Factory as FileFactory;
use Doxport\Metadata\Driver;
use Fhaculty\Graph\Set\Vertices;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class Pass implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Vertices
     */
    protected $vertices;

    /**
     * @var EntityGraph
     */
    protected $graph;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var boolean
     */
    protected $includeRoot = false;

    /**
     * Whether to export a graph image
     *
     * @var boolean
     */
    protected $exportGraph = false;

    /**
     * Whether to export a list of visited entities in order (constraints.txt)
     *
     * @var boolean
     */
    protected $exportConstraints = true;

    /**
     * Constructor
     *
     * @param Driver $driver
     * @param EntityGraph $graph
     * @param Action $action
     * @param Vertices $vertices
     */
    public function __construct(Driver $driver, EntityGraph $graph, Action $action, Vertices $vertices = null)
    {
        $this->driver  = $driver;
        $this->graph   = $graph;
        $this->action  = $action;
        $this->vertices = $vertices;
        $this->logger  = new NullLogger();
    }

    /**
     * Configures the graph object
     *
     * @return void
     */
    abstract protected function configureGraph();

    /**
     * @return mixed
     */
    public function run()
    {
        $this->configureGraph();
    }

    /**
     * @param FileFactory $fileFactory
     */
    public function setFileFactory(FileFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * @param boolean $include
     */
    public function setIncludeRoot($include)
    {
        $this->includeRoot = $include;
    }

    /**
     * @param boolean $export
     */
    public function setExportGraph($export)
    {
        $this->exportGraph = $export;
    }

    /**
     * @param boolean $export
     */
    public function setExportConstraints($export)
    {
        $this->exportConstraints = $export;
    }

    /**
     * @param Vertices $vertices
     * @return void
     */
    public function setVertices(Vertices $vertices)
    {
        $this->vertices = $vertices;
    }
}
