<?php

namespace Doxport\Action;

use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AbstractFile;
use Fhaculty\Graph\Walk;

/**
 * Writes the data to files, doesn't modify the data in the database
 */
class Export extends QueryAction
{
    /**
     * Properties to be cleared during export
     *
     * @var array
     */
    protected $clear = [];

    /**
     * @inheritDoc
     */
    protected function processQuery(JoinWalk $walk)
    {
        $this->debugMemory();

        if (!empty($this->clear[$walk->getTargetId()])) {
            $clearFile = $this->getClearFile($walk);
        } else {
            $clearFile = null;
        }

        $class = $this->driver->getEntityMetadata($walk->getTargetId())->getClassMetadata();

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
            $array = $this->entityToArray($entity);

            if ($clearFile) {
                $clear = $this->clear[$walk->getTargetId()];

                $this->writeClearedProperties($clearFile, $class, $entity, $clear['joinFields']);
                $this->clearProperties($class, $array, array_merge($clear['fields'], $clear['joinFields']));
            }

            $file->writeObject($array);  // Write to file

            $this->em->detach($entity);

            if ($i > $this->chunk->getEstimatedSize()) {
                $this->flush($file, $clearFile, $i);
                $this->em->clear();
            }

            $i++;
        }

        $entity = null;
        $array  = null;

        if ($i > 0) {
            // Remaining in current chunk
            $this->flush($file, $clearFile, $i);
        } elseif ($i == 0) {
            $this->logger->notice('No results for export');
        }

        $this->logger->notice('Done with {target}', ['target' => $walk->getTargetId()]);
        $this->close($file, $clearFile);
    }

    /**
     * Flushes the files
     *
     * @param AbstractFile      $file
     * @param AbstractFile|null $clearFile
     * @param int               $count
     * @return void
     */
    protected function flush(AbstractFile $file, AbstractFile $clearFile = null, $count = 0)
    {
        $this->logger->notice('  Flushing...');

        if ($count) {
            $this->chunk->begin();
        }

        $file->flush();

        if ($clearFile) {
            $clearFile->flush();
        }

        if ($count) {
            $this->chunk->end($count);
        }

        $this->logger->notice('  done.');
    }

    /**
     * Closes the files
     *
     * @param AbstractFile $file
     * @param AbstractFile|null $clearFile
     */
    protected function close(AbstractFile $file, AbstractFile $clearFile = null)
    {
        $file->close();

        if ($clearFile) {
            $clearFile->close();
        }
    }

    /**
     * Store properties to be cleared
     *
     * Because we're only exporting data, we don't need to actually update
     * the data at all for the clear to be respected. We just need to modify
     * what we write to the exported files.
     *
     * @param \Fhaculty\Graph\Walk $walk
     * @param array $fields
     * @param array $joinFields
     * @return mixed
     */
    public function processClear(Walk $walk, array $fields, array $joinFields)
    {
        $this->clear[$walk->getVertexSource()->getId()] = [
            'fields'     => $fields,
            'joinFields' => $joinFields
        ];
    }
}
