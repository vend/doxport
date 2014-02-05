<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;

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
        $iterator = $query->iterate(null, Query::HYDRATE_SIMPLEOBJECT);

        if (!$iterator->valid()) {
            $this->logger->notice('No results');
            $file->close();
            return;
        }

        $this->logger->notice('Iterating through results...');

        foreach ($iterator as $result) {
            $entity = $result[0];

            $file->writeObject($this->entityToArray($entity));
            $this->em->detach($entity);
        }

        // Remaining in current chunk
        $file->flush();
        $file->close();

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
    }
}
