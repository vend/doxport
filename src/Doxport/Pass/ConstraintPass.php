<?php

namespace Doxport\Pass;

use Fhaculty\Graph\Set\Vertices;
use Psr\Log\LogLevel;

class ConstraintPass extends Pass
{
    /**
     * Whether to export the constraint graph image
     *
     * @var boolean
     */
    protected $exportGraph = false;

    /**
     * @param boolean $export
     */
    public function setExportGraph($export)
    {
        $this->exportGraph = $export;
    }

    protected function configureGraph()
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
    }

    /**
     * @return \Fhaculty\Graph\Set\Vertices In work order to respect constraints
     */
    public function run()
    {
        parent::run();

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
        if (!$this->exportGraph) {
            return;
        }

        $path = $this->fileFactory->getPathForFile('constraints', 'png');
        $this->logger->info('Outputting constraint image to {path}', ['path' => $path]);

        $this->graph->export($path);
    }
}
