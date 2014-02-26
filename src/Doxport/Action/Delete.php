<?php

namespace Doxport\Action;

use Doctrine\ORM\Query;
use Doxport\Action\Base\QueryAction;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AsyncFile;
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
     * @param Walk $path
     * @param array $properties
     * @return mixed
     */
    public function processClear(Walk $path, array $properties)
    {
        $this->logger->notice('Doing clear of properties');

        // Prep work
        $walk = $this->getJoinWalk($path);
        $class = $this->driver->getEntityMetadata($walk->getTargetId())->getClassMetadata();
        $export = array_merge($properties, $class->getIdentifierFieldNames());

        // Get query
        $this->logger->notice('Getting select query for {target}', ['target' => $walk->getTargetId()]);
        $query = $walk->getQuery();
        $this->logger->info($query->getSQL());

        // Output join information
        $this->logger->info((string)$walk);

        // Get iterator
        $iterator = $query->iterate(null);

        // Output file information
        $file = $this->fileFactory->getFile($this->getClassName($walk->getTargetId()) . '.clear');
        $this->logger->notice('Outputting to {file}', ['file' => (string)$file]);

        // Iterate through results
        $this->logger->notice('Iterating through results...');
        $i = 0;

        foreach ($iterator as $result) {
            $entity = $result[0];

            $array = $this->entityToArray($entity, $export);
            $file->writeObject($array);  // Write to file

            foreach ($properties as $property) {
                if ($class->hasAssociation($property)) {
                    $association = $class->getAssociationMapping($property);
                    $class->setFieldValue($entity, $association['fieldName'], null);

                    foreach ($association['joinColumnFieldNames'] as $name) {
                        $class->setFieldValue($entity, $name, null);
                    }
                } else {
                    $class->setFieldValue($entity, $property, null);
                }
            }

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
