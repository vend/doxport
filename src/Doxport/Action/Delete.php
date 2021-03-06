<?php

namespace Doxport\Action;

use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AbstractFile;
use Fhaculty\Graph\Walk;

/**
 * Delete action
 *
 * Deletes rows of the database, but stores the content to alternative storage
 * first
 */
class Delete extends QueryAction
{
    /**
     * @param JoinWalk $walk
     * @return void
     */
    protected function processQuery(JoinWalk $walk)
    {
        $this->logger->notice('Doing deletion');

        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->debugQuery($query);

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null);

        // Output file information
        $file = $this->fileFactory->getFile($this->getClassName($walk->getTargetId()));
        $this->logger->notice('Outputting to {file}', ['file' => (string)$file]);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            $entity = $result[0];

            $array = $this->entityToArray($entity);
            $file->writeObject($array);  // Write to file

            $this->em->remove($entity);  // Queue delete
            $i++;

            if ($i > $this->chunk->getEstimatedSize()) {
                $this->flush($file, $i); // Actually apply changes
                $i = 0;
            }
        }

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush($file, $i);
        } elseif ($i == 0) {
            $this->logger->notice('No results.');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
        $file->close();
    }

    /**
     * @param AbstractFile $file
     * @param integer $count The number of queued operations on the unit of work
     * @throws \Doxport\Exception\IOException
     * @return void
     */
    protected function flush(AbstractFile $file, $count = null)
    {
        $this->logger->notice('  Flushing and syncing...');

        $file->flush();
        $file->sync();

        $this->logger->notice('  done. Committing deletes...');

        $this->chunk->begin();
        $this->em->flush();
        $this->em->clear();
        $this->chunk->end($count);

        $this->logger->notice('  done.');
    }

    /**
     * Process the clear pass
     *
     * @param Walk $path
     * @param array $fields
     * @param array $joinFields
     * @return mixed
     */
    public function processClear(Walk $path, array $fields, array $joinFields)
    {
        $this->logger->notice('Doing clear of properties');

        // Prep work
        $walk = $this->getJoinWalk($path);
        $class = $this->driver->getEntityMetadata($walk->getTargetId())->getClassMetadata();

        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->debugQuery($query);

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null);

        // Output file information
        $file = $this->getClearFile($walk);
        $this->logger->notice('Outputting to {file}', ['file' => (string)$file]);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            /** @var object $entity */
            $entity = $result[0];

            $this->writeClearedProperties($file, $class, $entity, $joinFields);
            $this->clearProperties($class, $entity, array_merge($fields, $joinFields));

            $this->em->persist($entity);

            if ($i > $this->chunk->getEstimatedSize()) {
                $this->flush($file, $i); // Actually apply changes
                $i = 0;
            }

            $i++;
        }

        if ($i > 0) {
            $this->flush($file, $i);
        } elseif ($i == 0) {
            $this->logger->notice('No results to delete');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
        $file->close();
    }
}
