<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\EntityGraph;
use Doxport\File\Factory;
use Doxport\Metadata\Driver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Pass implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var boolean
     */
    protected $includeRoot = false;

    /**
     * Constructor
     *
     * @param Driver      $driver
     * @param EntityGraph $graph
     * @param Action      $action
     * @param array       $options
     */
    public function __construct(Driver $driver, EntityGraph $graph, Action $action)
    {
        $this->driver  = $driver;
        $this->graph   = $graph;
        $this->action  = $action;
    }

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * @param Factory $fileFactory
     */
    public function setFileFactory(Factory $fileFactory)
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
}
