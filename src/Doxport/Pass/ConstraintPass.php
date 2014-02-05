<?php

namespace Doxport\Pass;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Doxport\EntityGraph;
use Fhaculty\Graph\Exception\UnexpectedValueException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Walk;
use Psr\Log\LogLevel;

class ConstraintPass extends Pass
{
    /**
     * @return \Fhaculty\Graph\Set\Vertices In work order to respect constraints
     */
    public function run()
    {
        $this->logger->log(LogLevel::INFO, 'Filtering for covered, supported associations');
        $this->graph->from($this->driver, function (array $association) {
            // Ignore self-joins
            if ($association['sourceEntity'] == $association['targetEntity']) {
                return false;
            }

            return $this->driver->isSupportedAssociation($association)
                && $this->driver->isColumnOwnerAssociation($association)
                && $this->driver->isConstraintAssociation($association);
        });

        $this->logger->log(LogLevel::INFO, 'Filtering for connected entities');
        $this->graph->filterConnected();

        $this->outputGraph();

        $this->logger->log(LogLevel::INFO, 'Producing topological sort for dependency order');
        $vertices = $this->graph->topologicalSort();

        $this->outputVertices($vertices);

        return $vertices;
    }

    protected function outputVertices(Vertices $vertices)
    {
        $path = $this->fileFactory->getPathForFile('constraints', 'txt');
        $this->logger->info('Outputting constraint text to {path}', ['path' => $path]);
        file_put_contents($path, implode("\n", $vertices->getIds()));
    }

    protected function outputGraph()
    {
        $path = $this->fileFactory->getPathForFile('constraints', 'png');
        $this->logger->info('Outputting constraint image to {path}', ['path' => $path]);

        $this->graph->export($path);
    }
}
