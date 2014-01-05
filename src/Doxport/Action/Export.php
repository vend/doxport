<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Criteria;

class Export extends FileAction
{
    use JoiningAction;

    protected function getFilePath(Criteria $criteria)
    {
        return 'build/export/' . date('YmdHis') . '/' . strtolower($criteria->getEntityClassName()) . '.sql';
    }

    /**
     * @return integer
     */
    protected function getType()
    {
        return Action::TYPE_DFS;
    }

    protected function doProcess(Criteria $criteria)
    {
        $query = $this->getSelectQuery($criteria);
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);

        foreach ($iterator as $result) {
            $entity = $result[0];
            $serialized = $this->serialize($entity);

            $this->file->writeCsvRow($serialized);

            $this->em->detach($entity); // Allow GC
        }

        $this->file->flush();
    }
}
