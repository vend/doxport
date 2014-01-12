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

            $allowed =
                ($a = $this->driver->isSupportedAssociation($association))
                && ($b = $this->driver->isCoveredAssociation($association))
                && ($c = $this->driver->isConstraintAssociation($association));

            if (!$allowed) {
                $a = $this->driver->isSupportedAssociation($association);
                $b = $this->driver->isCoveredAssociation($association);
                $c = $this->driver->isConstraintAssociation($association);
            }

            return $allowed;
        });

        $this->graph->filterConnected();

        // Debugging
        $this->graph->export('build/c.png');
        echo (string)$this->graph . "\n";

        file_put_contents('build/c.dot', (string)$this->graph);

        $sort = [];

        try {
            $sort = $this->graph->topologicalSort();
        } catch (UnexpectedValueException $e) {
            throw $e;
        }

        foreach ($sort as $vertex) {
            echo $vertex->getId() . " -> \n";
        }

        return $sort;
    }
}
