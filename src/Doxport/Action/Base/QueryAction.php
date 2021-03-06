<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doxport\Doctrine\AliasGenerator;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AbstractFile;
use Doxport\Pass\ClearPass;
use Doxport\Util\EntityArrayHelper;
use Fhaculty\Graph\Walk;

abstract class QueryAction extends Action
{
    /**
     * @var array
     */
    protected $rootCriteria = [];

    /**
     * @param string $column
     * @param mixed  $value
     * @return void
     */
    public function addRootCriteria($column, $value)
    {
        $this->rootCriteria[] = [
            'column' => $column,
            'value'  => $value
        ];
    }

    /**
     * @param Walk $path
     * @return void
     */
    public function process(Walk $path)
    {
        $walk = $this->getJoinWalk($path);
        $this->processQuery($walk);
    }

    /**
     * @param Walk $path
     * @return JoinWalk
     */
    protected function getJoinWalk(Walk $path)
    {
        $walk = new JoinWalk(
            $path,
            $this->em->createQueryBuilder(),
            new AliasGenerator()
        );

        foreach ($this->rootCriteria as $criteria) {
            $walk->whereRootFieldEq($criteria['column'], $criteria['value']);
        }

        return $walk;
    }

    /**
     * @param JoinWalk $walk
     * @return AbstractFile
     */
    protected function getClearFile(JoinWalk $walk)
    {
        return $this->fileFactory->getFile(
            $this->getClassName($walk->getTargetId()) . ClearPass::FILE_SUFFIX
        );
    }

    /**
     * @param AbstractFile     $file
     * @param ClassMetadata $metadata
     * @param object        $entity
     * @param array         $properties
     */
    protected function writeClearedProperties(AbstractFile $file, ClassMetadata $metadata, $entity, array $properties)
    {
        $cleared = $this->entityToArray($entity, $properties);

        if ($cleared) {
            $file->writeObject([
                'identifiers' => $this->entityToArray($entity, $metadata->getIdentifierFieldNames()),
                'cleared'     => $cleared
            ]);
        }
    }

    /**
     * @param ClassMetadata $metadata
     * @param object|array  $entity
     * @param array         $properties
     */
    protected function clearProperties(ClassMetadata $metadata, &$entity, array $properties)
    {
        if (is_array($entity)) {
            foreach ($properties as $property) {
                $entity[$property] = null;
            }
        } else {
            foreach ($properties as $property) {
                if ($metadata->hasField($property) || $metadata->hasAssociation($property)) {
                    $metadata->setFieldValue($entity, $property, null);
                }
            }
        }
    }

    /**
     * @param object $entity
     * @param array  $fields
     * @return array
     */
    protected function entityToArray($entity, array $fields = [])
    {
        $helper = new EntityArrayHelper($this->em);
        $array = $helper->toArray($entity, $fields);

        return $array;
    }

    /**
     * Prints debugging information about the given query
     *
     * @param Query $query
     */
    protected function debugQuery(Query $query)
    {
        if (!$this->options['verbose']) {
            return;
        }

        $sql = $query->getSQL();

        if (is_array($sql)) {
            $sql = implode('; ', $sql);
        }

        $this->logger->info($sql);
    }

    /**
     * Processes the main part of the action
     *
     * @param JoinWalk $walk
     * @return mixed
     */
    abstract protected function processQuery(JoinWalk $walk);

    /**
     * For the delete to work, foreign key constraints that can't be cleared
     * as part of the main delete processing will be cleared in the database
     * first. This is usually things like relational cycles.
     *
     * Here, we make the same select the main processing is going to. Then we
     * store the cleared properties in the file and clear the properties from
     * the database. (Actually making an update.)
     *
     * @param \Fhaculty\Graph\Walk $walk
     * @param array $fields
     * @param array $joinFields
     * @return mixed
     */
    abstract public function processClear(Walk $walk, array $fields, array $joinFields);
}
