<?php

namespace Doxport;

use Doxport\Metadata\Driver;
use Fhaculty\Graph\Graph;

class GraphFactory
{
    protected $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function getConstraintGraph()
    {
        $graph = new EntitiyGraph();


    }


}
