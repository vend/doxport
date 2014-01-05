<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Criteria;
use Exception;

class ArchiveDelete extends FileAction
{
    use JoiningAction;

    /**
     * @return integer
     */
    protected function getType()
    {
        return Action::TYPE_DFS;
    }

    protected function getFilePath(Criteria $criteria)
    {
        return 'build/archived/' . date('YmdHis') . '/' . strtolower($criteria->getEntityClassName()) . '.sql';
    }

    protected function doProcess(Criteria $criteria)
    {
        // Do initial query for entities to process
        $query = $this->getSelectQuery($criteria);
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);

        // Start a transaction
        $connection = $this->em->getConnection();
        $connection->beginTransaction(); // Don't want transactional()

        try {
            foreach ($iterator as $result) {
                $entity = $result[0];

                $serialized = $this->serialize($entity);

                $this->file->writeCsvRow($serialized);  // Write to file
                $this->delete($criteria, $entity); // Send DELETE query, TODO return value

                $this->em->detach($entity); // Allow GC
            }

            // Sync the results to the file
            $this->file->flush();
            $this->file->sync(function ($data, $result) {
                if (!$result || true) { // TODO Debugging or branch
                    throw new IOException('Could not write to result file, should skip transaction');
                }
            });
            $this-            >file->close();

            $connection->commit();   // db commit
        } catch (Exception $e) {
            $connection->rollBack(); // or rollback
            throw $e;
        }
    }

    /**
     * Does a single row delete based on the identifiers of the row
     *
     * @param Criteria $criteria
     * @param $entity
     * @return mixed
     */
    protected function delete(Criteria $criteria, $entity)
    {
        $unit = $this->em->getUnitOfWork();

        $qb = $this->em->createQueryBuilder()
            ->delete()
            ->from($criteria->getEntityName(), $criteria->getQueryAlias());

        $data = $unit->getOriginalEntityData($entity);

        foreach ($criteria->getMetadata()->getClassMetadata()->getIdentifierFieldNames() as $idField) {
            $qb->andWhere($qb->expr()->eq($criteria->getQueryAlias() . '.' . $idField, ':' . $criteria->getQueryAlias() . $idField));
            $qb->setParameter(':' . $criteria->getQueryAlias() . $idField, $data[$idField]);
        }

        return $qb->getQuery()->execute();
    }
}
