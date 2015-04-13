<?php

namespace Doxport\Action;

use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Fhaculty\Graph\Walk;

/**
 * Hard delete action
 *
 * Deletes rows without backing them up to alternative storage first
 */
class HardDelete extends QueryAction
{
    /**
     * @inheritDoc
     */
    protected function processQuery(JoinWalk $walk)
    {
        $this->logger->notice('Doing hard deletion');

        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->debugQuery($query);

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;
        $total = 0;

        foreach ($iterator as $result) {
            $entity = $result[0];

            $this->em->remove($entity); // Queue delete
            $total++;

            if ($i > $this->chunk->getEstimatedSize()) {
                $this->flush($i); // Actually apply changes
                $i = 0;
            }

            $i++;
        }

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush($i);
        } elseif ($i == 0) {
            $this->logger->notice('No results.');
        }

        $this->logger->notice('Done with {target} ({total} deleted)', ['target' => $walk->getTargetId(), 'total' => $total]);
    }

    /**
     * @param integer $count The number of queued operations on the unit of work
     * @return void
     */
    protected function flush($count = null)
    {
        $this->logger->notice('  Committing deletes...');

        $this->chunk->begin();
        $this->em->flush();
        $this->em->clear();
        $this->chunk->end($count);

        $this->logger->notice('  done.');
    }

    /**
     * @inheritDoc
     */
    public function processClear(Walk $path, array $fields, array $joinFields)
    {
        $this->logger->notice('Doing clear of properties');

        // Prep work
        $walk  = $this->getJoinWalk($path);
        $class = $this->driver->getEntityMetadata($walk->getTargetId())->getClassMetadata();

        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->debugQuery($query);

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            /** @var object $entity */
            $entity = $result[0];

            $this->clearProperties($class, $entity, array_merge($fields, $joinFields));

            $this->em->persist($entity);
            $i++;

            if ($i > $this->chunk->getEstimatedSize()) {
                $this->flush($i); // Actually apply changes
                $i = 0;
            }
        }

        if ($i > 0) {
            $this->flush($i);
        } elseif ($i == 0) {
            $this->logger->notice('No results to delete');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
    }
}
