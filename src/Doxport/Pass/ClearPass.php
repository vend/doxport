<?php

namespace Doxport\Pass;

use Fhaculty\Graph\Vertex;

/**
 * Pass to clear properties with update, ahead of main action
 */
class ClearPass extends JoinPass
{
    const FILE_SUFFIX = '.clear';

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
                if (!$property->hasAnnotation('Doxport\Annotation\Clear')) {
                    continue;
                }

                $properties[] = $property->getName();

                foreach ($property->getJoinColumnFieldNames() as $field) {
                    $properties[] = $field;
                }
            }

            if ($properties) {
                $this->action->processClear($this->getWalkForVertex($vertex), $properties);
            }
        }
    }

}
