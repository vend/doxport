<?php

namespace Doxport\Pass;

use Doxport\EntityGraph;
use Doxport\Metadata\Driver;

abstract class Pass
{
    /**
     * @var EntityGraph
     */
    protected $graph;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * Constructor
     *
     * @param Driver      $driver
     * @param EntityGraph $graph
     */
    public function __construct(Driver $driver, EntityGraph $graph)
    {
        $this->driver = $driver;
        $this->graph  = $graph;
    }

    /**
     * @return mixed
     */
    abstract public function run();
}
