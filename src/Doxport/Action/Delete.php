<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AsyncFile;
use Doxport\Pass\ClearPass;
use Fhaculty\Graph\Walk;

/**
 * Delete action
 */
class Delete extends QueryAction
{
    const CHUNK_SIZE = 100;

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
        $this->logger->info($query->getSQL());

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

            if ($i > self::CHUNK_SIZE) {
                $this->flush($file); // Actually apply changes
                $i = 0;
            }

            $i++;
        }

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush($file);
        } elseif ($i == 0) {
            $this->logger->notice('No results.');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
        $file->close();
    }

    /**
     * @param AsyncFile $file
     * @return void
     */
    protected function flush(AsyncFile $file)
    {
        $this->logger->notice('  Flushing and syncing...');

        $file->flush();
        $file->sync();

        $this->logger->notice('  done. Committing deletes...');

        $this->em->flush();
        $this->em->clear();

        $this->logger->notice('  done.');
    }

    /**
     * Process the clear pass immediately
     *
     * For the delete to work, foreign key constraints that can't be cleared
     * as part of the main delete processing will be cleared in the database
     * first. This is usually things like relational cycles.
     *
     * Here, we make the same select the main processing is going to. Then we
     * store the cleared properties in the file and clear the properties from
     * the database. (Actually making an update.)
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
        $this->logger->info($query->getSQL());

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
            $entity = $result[0];

            $this->writeClearedProperties($file, $class, $entity, $joinFields);
            $this->clearProperties($class, $entity, array_merge($fields, $joinFields));

            $this->em->persist($entity);

            if ($i > self::CHUNK_SIZE) {
                $this->flush($file); // Actually apply changes
                $i = 0;
            }

            $i++;
        }

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush($file);
        } elseif ($i == 0) {
            $this->logger->notice('No results.');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
        $file->close();
    }
}
