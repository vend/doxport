<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doxport\Doctrine\AliasGenerator;
use Doxport\Doctrine\JoinWalk;
use Doxport\File\AsyncFile;
use Doxport\Metadata\Driver;
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
     * @var array
     */
    protected $processedSelfJoins = [];

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
     * @param array $association
     */
    public function processSelfJoin(Walk $path, array $association)
    {
//        $walk = $this->getJoinWalk($path);
//
//        if ($association['type'] == ClassMetadata::ONE_TO_MANY) {
//            // Skipping because inverse of one that's already processed
//            return;
//        }
//
//        $walk->addSelfJoinNull($association['inversedBy'], $association['sourceToTargetKeyColumns']);
//
//        // One level of self-join
//        $this->processQuery($walk);
//
//        // Two levels of self-join
//        $this->processQuery($walk);
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
     * @return AsyncFile
     */
    protected function getClearFile(JoinWalk $walk)
    {
        return $this->fileFactory->getFile(
            $this->getClassName($walk->getTargetId()) . ClearPass::FILE_SUFFIX
        );
    }

    /**
     * @param AsyncFile     $file
     * @param ClassMetadata $metadata
     * @param object        $entity
     * @param array         $properties
     */
    protected function writeClearedProperties(AsyncFile $file, ClassMetadata $metadata, $entity, array $properties)
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
     * @param JoinWalk $walk
     * @return mixed
     */
    abstract protected function processQuery(JoinWalk $walk);

    /**
     * @param \Fhaculty\Graph\Walk $walk
     * @param array $fields
     * @param array $joinFields
     * @return mixed
     */
    abstract public function processClear(Walk $walk, array $fields, array $joinFields);
}
