<?php

namespace Doxport\Pass;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;
use Fhaculty\Graph\Exception\UnexpectedValueException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Walk;

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

        $vertices = $this->graph->topologicalSort();

        $this->outputVertices($vertices);
        $this->outputGraph();

        return $vertices;
    }

    protected function outputVertices(Vertices $vertices)
    {
        $file = $this->action->getFileInstance('constraints.txt');
        $this->logger->info('Outputting constraint text to {path}', ['path' => $file->getPath()]);

        foreach ($vertices as $vertex) {
            $file->writeln($vertex->getId());
        }

        $file->close();
    }

    protected function outputGraph()
    {
        $path = $this->action->getFilePath() . '/constraints.png';
        $this->logger->info('Outputting constraint image to {path}', ['path' => $path]);

        $this->graph->export($path);
    }
}
