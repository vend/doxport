<?php

namespace Doxport\Pass;

use Fhaculty\Graph\Vertex;

class ClearPass extends JoinPass
{
    /**
     * @return void
     */
    public function run()
    {
        parent::configureGraph();

        foreach ($this->vertices as $vertex) {
            /* @var $vertex Vertex */
            if ($vertex->getId() == $this->graph->getRoot()) {
                if (!$this->includeRoot) {
                    continue;
                }
            }

            $entity     = $vertex->getId();
            $metadata   = $this->driver->getEntityMetadata($entity);
            $properties = [];

            foreach ($metadata->getProperties() as $property) {
                if ($property->hasAnnotation('Doxport\Annotation\Clear')) {
                    $properties[] = $property->getName();
                }
            }

            if ($properties) {
                $this->action->processClear($this->getWalkForVertex($vertex), $properties);
            }
        }
    }

}
