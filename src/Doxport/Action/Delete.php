<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\Util\AsyncFile;

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
        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->logger->info($query->getSQL());

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);

        // Output file information
        $file = $this->fileFactory->getFile($this->getClassName($walk->getTargetId()));
        $this->logger->notice('Outputting to {file}', ['file' => (string)$file]);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            $entity = $result[0];

            $file->writeObject($this->entityToArray($entity));  // Write to file
            $this->em->remove($entity);                     // Queue delete

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
        $this->logger->notice('  Flushing and committing...');

        $file->flush();
        $file->sync();

        $this->logger->notice('    done with flush...');

        $this->em->flush();
        $this->em->clear();

        $this->logger->notice('    done with commit.');
    }
}
