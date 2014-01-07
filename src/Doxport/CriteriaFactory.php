<?php

namespace Doxport;

use Doxport\Metadata\Entity;

class CriteriaFactory
{
    public function get(Entity $metadata)
    {
        $criteria = new Criteria();
        $criteria->setEntity($metadata);

        return $criteria;
    }
}
