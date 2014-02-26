<?php

namespace Doxport\Action;

use Doxport\Action\Base\Action;
use Doxport\Pass\ClearPass;
use Doxport\Util\EntityArrayHelper;
use LogicException;

class Import extends Action
{
    /**
     * @var string
     */
    protected $constraintPath;

    /**
     * @var array
     */
    protected $constraints = [];

    /**
     * @return void
     */
    public function run()
    {
        $this->validate();
        $this->parseConstraints();

        $this->importPass();
        $this->updateClearedPass();
    }

    /**
     * Imports the entities
     */
    protected function importPass()
    {
        $this->logger->info('Doing initial import pass');

        foreach ($this->constraints as $constraint) {
            $class = $this->getClassName($constraint);

            $file = $this->fileFactory->getFile($class);
            $objects = $file->readObjects();

            $this->process($constraint, $objects);
        }

        $this->logger->info('Initial import pass done');
    }

    /**
     * Updates entities based on .clear files
     */
    protected function updateClearedPass()
    {
        $this->logger->info('Doing secondary pass to fill in pre-cleared values');

        foreach ($this->constraints as $constraint) {
            $class = $this->getClassName($constraint);

            $file = $this->fileFactory->getFile($class . ClearPass::FILE_SUFFIX);
            $objects = $file->readObjects();

            if (!$objects) {
                continue;
            }

            $this->processUpdate($constraint, $objects);
        }

        $this->logger->info('Secondary pass done');
    }

    /**
     * Persists the given entities
     *
     * @param string $entityName
     * @param array  $entities
     */
    protected function process($entityName, array $entities)
    {
        $this->logger->notice('Processing import of {entityName}', ['entityName' => $entityName]);

        if (!$entities) {
            $this->logger->notice('  No entities to process');
            return;
        }

        $helper = new EntityArrayHelper($this->em);

        $i = 0;
        foreach ($entities as $values) {
            $entity = $helper->toEntity($entityName, $values);

            // Save entity
            $this->em->persist($entity);
            $i++;
        }

        $this->logger->notice('  {i} entities processed', ['i' => $i]);
        $this->em->flush();
    }

    /**
     * Updates the given entities
     *
     * @param string $entityName
     * @param array  $updates
     */
    protected function processUpdate($entityName, array $updates)
    {
        $class = $this->driver->getEntityMetadata($entityName)->getClassMetadata();

        foreach ($updates as $update) {
            if (empty($update['identifiers']) || empty($update['cleared'])) {
                $this->logger->warning('Skipping update of {entity}: nothing to do', ['entity' => $entityName]);
                continue;
            }

            $entity = $this->em->find($entityName, $update['identifiers']);

            if (!$entity) {
                $this->logger->warning('Cannot find {entity} to update, skipping update', ['entity' => $entityName]);
            }

            // @todo Doesn't support foreign keys in identifier
            // @todo Doesn't support multiple join columns in association

            foreach ($update['cleared'] as $field => $value) {
                if ($class->hasField($field)) {
                    $class->setFieldValue($entity, $field, $value);
                } elseif ($class->hasAssociation($class->getFieldForColumn($field))) {
                    $association = $class->getAssociationMapping($class->getFieldForColumn($field));
                    $reference = $this->em->getReference($association['targetEntity'], $value);
                    $class->setFieldValue($entity, $association['fieldName'], $reference);
                } else {
                    $this->logger->error('Could not fill in {field} on {entity}: unknown field', ['field' => $field, 'entity' => $entityName]);
                }
            }

            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    /**
     * @throws LogicException
     * @return void
     */
    protected function validate()
    {
        if (!is_dir($this->fileFactory->getPath())) {
            throw new LogicException('Cannot find data directory: ' . $this->fileFactory->getPath());
        }

        $this->constraintPath = $this->fileFactory->getPathForFile('constraints', 'txt');

        if (!is_readable($this->constraintPath)) {
            throw new LogicException('Unreadable constraints.txt in data directory - invalid data dir?');
        }
    }

    /**
     * Parses the constraints file to find the order to import data
     */
    protected function parseConstraints()
    {
        $contents = file_get_contents($this->constraintPath);
        $contents = explode(PHP_EOL, $contents);
        $this->constraints = array_reverse($contents);
    }
}
