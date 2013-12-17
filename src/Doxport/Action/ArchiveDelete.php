<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Criteria;

class ArchiveDelete extends Action
{
    use JoiningAction;

    const CHUNK_SIZE = 100;

    /**
     * Directory to export to
     *
     * @var string
     */
    protected $to;

    protected function configure()
    {
        $this->to = 'build/export/' . date('YmdHis');
    }

    /**
     * @return integer
     */
    protected function getType()
    {
        return Action::TYPE_DFS;
    }

    public function run()
    {
        if (!is_dir($this->to)) {
            mkdir($this->to, 0644, true);
        }

        parent::run();
    }

    protected function process(Criteria $criteria)
    {
        $file   = $this->to . '/' . $criteria->getEntityClassName();
        $handle = fopen($file, 'a');

        $query = $this->getQuery($criteria);
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);

        foreach ($iterator as $result) {
            $entity = $result[0];
            $serialized = $this->serialize($entity);

            fputcsv($handle, $serialized);
            fflush($handle);

            $this->em->detach($entity); // Allow GC
        }

        fclose($handle);
    }

    protected function getQuery(Criteria $criteria)
    {
        $qb = $this->em->createQueryBuilder();

        $this->apply($criteria, $qb);

        return $qb->getQuery();
    }
}
