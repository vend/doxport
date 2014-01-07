<?php

namespace Doxport\Test;

use Doxport\CriteriaFactory;
use Doxport\Metadata\Entity;

class MockCriteriaFactory extends CriteriaFactory
{
    protected $fixture = [];

    public function __construct(array $fixture = [])
    {
        $this->fixture = $fixture;
    }

    public function get(Entity $metadata)
    {
        $b = 2;
    }
}
