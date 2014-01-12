<?php

namespace Doxport\Pass;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;
use Fhaculty\Graph\Exception\UnexpectedValueException;
use Fhaculty\Graph\Graph;

class ConstraintPass extends Pass
{
    /**
     * @return \Fhaculty\Graph\Set\Vertices In work order to respect constraints
     */
    public function run()
    {
        $this->graph->from($this->driver, function (array $association) {
            // Ignore self-joins
            if ($association['sourceEntity'] == $association['targetEntity']) {
                return false;
            }

            return ($this->driver->isSupportedAssociation($association))
                && ($this->driver->isCoveredAssociation($association))
                && ($this->driver->isConstraintAssociation($association));
        });

        $this->graph->filterConnected();

        return $this->graph->topologicalSort();
    }
}
