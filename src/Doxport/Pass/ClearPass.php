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

            $fields     = [];
            $joinFields = [];

            foreach ($metadata->getProperties() as $property) {
                if (!$property->hasAnnotation('Doxport\Annotation\Clear')) {
                    continue;
                }

                $fields[] = $property->getName();

                foreach ($property->getJoinColumnFieldNames() as $field) {
                    $joinFields[] = $field;
                }
            }

            if ($fields || $joinFields) {
                $this->action->processClear($this->getWalkForVertex($vertex), $fields, $joinFields);
            }
        }
    }

}
