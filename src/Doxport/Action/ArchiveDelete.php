<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Criteria;
use \Exception;
use Doxport\Util\SimpleObjectSerializer;

class ArchiveDelete extends FileAction
{
    const CHUNK_SIZE = 100;

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
        $this->output->writeln('Collecting criteria to produce SELECT for ' . $criteria->getEntityName());
        $query = $this->getSelectQuery($criteria);

        $this->output->write('Executing...');
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);
        $this->output->writeln('done.');

        $i = 0;

        $this->output->write('Dumping a chunk to disk...');

        foreach ($iterator as $result) {
            $entity = $result[0];

            $this->file->writeCsvRow($this->serialize($entity));  // Write to file
            $this->em->remove($entity);                           // Queue delete

            if ($i > self::CHUNK_SIZE) {
                $this->output->writeln('done.');

                $this->flush(); // Actually apply changes
                $i = 0;
            }

            $i++;
        }

        $this->output->writeln('done.');

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush();
        }

        $this->file->close();
    }

    protected function flush()
    {
        $this->output->write('Flushing and commiting...');

        $this->file->flush();
        $this->file->sync();

        $this->output->write('done with flush...');

        $this->em->flush();
        $this->em->clear();

        $this->output->writeln('done with commit.');
    }

    /**
     * Does a single row delete based on the identifiers of the row
     *
     * @param Criteria $criteria
     * @param $entity
     * @return mixed
     */
//    protected function delete(Criteria $criteria, $entity)
//    {
//        $unit = $this->em->getUnitOfWork();
//
//        $qb = $this->em->createQueryBuilder()
//            ->delete()
//            ->from($criteria->getEntityName(), $criteria->getQueryAlias());
//
//        $data = $unit->getOriginalEntityData($entity);
//
//        foreach ($criteria->getMetadata()->getClassMetadata()->getIdentifierFieldNames() as $idField) {
//            $qb->andWhere($qb->expr()->eq($criteria->getQueryAlias() . '.' . $idField, ':' . $criteria->getQueryAlias() . $idField));
//            $qb->setParameter(':' . $criteria->getQueryAlias() . $idField, $data[$idField]);
//        }
//
//        return $qb->getQuery()->execute();
//    }

    protected function serialize($entity)
    {
        if (method_exists($entity, '__sleep')) {
            return $entity->__sleep();
        }

        $serializer = new SimpleObjectSerializer($this->em);
        return $serializer->serialize($entity);
    }
}
