<?php

namespace Doxport\Pass;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;

class ConstraintPass extends Pass
{
    /**
     * @return \Fhaculty\Graph\Set\Vertices In work order to respect constraints
     */
    public function run()
    {
        $this->graph->from($this->driver, function (array $association) {
            $allowed =
                ($a = $this->driver->isSupportedAssociation($association))
                && ($b = $this->driver->isCoveredAssociation($association))
                && ($c = $this->driver->isConstraintAssociation($association))
                && ($d = !$this->driver->isOptionalAssociation($association));

            if (!$allowed) {
                $a = $this->driver->isSupportedAssociation($association);
                $b = $this->driver->isCoveredAssociation($association);
                $c = $this->driver->isConstraintAssociation($association);
                $d = !$this->driver->isOptionalAssociation($association);
            }

            return $allowed;
        });

        $this->graph->filterConnected();

        return $this->graph->topologicalSort();
    }
}
