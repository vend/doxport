<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AsyncFile;
use Fhaculty\Graph\Walk;

/**
 * Writes the data to files, doesn't modify the data in the database
 */
class Export extends QueryAction
{
    /**
     * @param JoinWalk $walk
     * @return void
     */
    protected function processQuery(JoinWalk $walk)
    {
        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();

        // Output join information
        $this->logger->info((string)$walk);

        // Output file information
        $file = $this->fileFactory->getFile($this->getClassName($walk->getTargetId()));
        $this->logger->notice('Outputting to {file}', ['file' => (string)$file]);

        // Iterate through results
        $iterator = $query->iterate(null);

        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            $entity = $result[0];
            $array  = $this->entityToArray($entity);

            $file->writeObject($array);  // Write to file
            $this->em->detach($entity);

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

        $this->logger->notice('  done.');
    }

    /**
     * We don't clear properties when just doing an export, because its not
     * necessary.
     *
     * @param \Fhaculty\Graph\Walk $walk
     * @param array $properties
     * @return mixed
     */
    public function processClear(Walk $walk, array $properties)
    {
        // Nothing to do
    }
}
