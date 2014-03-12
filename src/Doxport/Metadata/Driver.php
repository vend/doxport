<?php

namespace Doxport\Metadata;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use \LogicException;

class Driver
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Entity[]
     */
    protected $entities = [];

    /**
     * @var AnnotationDriver
     */
    protected $doctrine;

    /**
     * @param EntityManager $em
     * @throws LogicException
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->doctrine = $this->em->getConfiguration()->getMetadataDriverImpl();

        if (!($this->doctrine instanceof AnnotationDriver)) {
            throw new LogicException('Doxport expects Doctrine2 to be using an annotation metadata driver');
        }
    }

    /**
     * Entities as opposed to mapped superclasses
     *
     * @return string[]
     */
    public function getEntityNames()
    {
        $reader = $this->doctrine->getReader();

        return array_filter($this->doctrine->getAllClassNames(), function ($class) use ($reader) {
            $annotations = $reader->getClassAnnotations(new \ReflectionClass($class));

            foreach ($annotations as $annotation) {
                // Exclude mapped superclasses
                if (get_class($annotation) == 'Doctrine\ORM\Mapping\MappedSuperclass') {
                    return false;
                }

                if (get_class($annotation) == 'Doxport\Annotation\Shared') {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @param array $association
     * @return boolean
     */
    public function isOptionalAssociation(array $association)
    {
        if ($association['type'] == ClassMetadata::MANY_TO_MANY) {
            if (empty($association['joinTable'])) {
                return true;
            }

            $forward = $association['joinTable']['joinColumns'];
            $inverse = $association['joinTable']['inverseJoinColumns'];

            $columns = array_merge($forward, $inverse);
        } else {
            if (empty($association['joinColumns'])) {
                return true; // Assume optional
            }

            $columns = $association['joinColumns'];
        }

        foreach ($columns as $joinColumn) {
            if (isset($joinColumn['nullable']) && !$joinColumn['nullable']) {
                // nullable is true by default
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $association
     * @return boolean
     */
    public function isSupportedAssociation(array $association)
    {
        if ($association['type'] == ClassMetadata::MANY_TO_MANY) {
            return false;
        }

        // Ignore self-joins
        if ($association['sourceEntity'] == $association['targetEntity']) {
            return false;
        }

        $property = $this->getEntityMetadata($association['sourceEntity'])
            ->getProperty($association['fieldName']);

        if ($property && $property->hasAnnotation('Doxport\Annotation\Exclude')) {
            return false;
        }

        return true;
    }

    /**
     * @param array $association
     * @return bool
     * @todo Whether the association is covered by a relational constraint preventing deletion
     *       (not including onDelete={cascade, set_null, ...?}
     */
    public function isConstraintAssociation(array $association)
    {
        return true;
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     * @throws LogicException
     */
    protected function getAnnotationReader()
    {
        return $this->doctrine->getReader();
    }

    /**
     * @param string $entity
     * @return Entity
     */
    public function getEntityMetadata($entity)
    {
        if (isset($this->entities[$entity])) {
            return $this->entities[$entity];
        }

        $reader = $this->getAnnotationReader();
        $meta = new Entity($this->em->getClassMetadata($entity));

        foreach ($meta->getClassMetadata()->getReflectionProperties() as $name => $property) {
            if (!$property) {
                continue;
            }

            $relevant = array_filter($reader->getPropertyAnnotations($property), function ($value) {
                return implode('\\', array_slice(explode('\\', get_class($value)), 0, -1)) == 'Doxport\\Annotation';
            });

            if (!$relevant) {
                continue;
            }

            $property = new Property(
                $name,
                $relevant,
                $meta->getClassMetadata()->getAssociationMapping($property->name)
            );

            $meta->addProperty($property);
        }

        return $this->entities[$entity] = $meta;
    }

    /**
     * @param string $sourceEntity
     * @param array $joinColumnFieldNames
     * @return bool
     */
    public function isCovered($sourceEntity, $joinColumnFieldNames)
    {
        // Reindex numerically
        $search   = array_values($joinColumnFieldNames);

        // Get table and list of indexes
        $table    = $this->getEntityMetadata($sourceEntity)->getClassMetadata()->getTableName();
        $indexes  = $this->em->getConnection()->getSchemaManager()->listTableIndexes($table);

        // Look for an index with all of $search
        foreach ($indexes as $index) {
            $columns = $index->getColumns();

            foreach ($search as $i => $name) {
                if ($columns[$i] != $name) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $association
     * @return bool
     */
    public function isCoveredAssociation(array $association)
    {
        if (!$this->isColumnOwnerAssociation($association)) {
            return false;
        }

        return $this->isCovered($association['sourceEntity'], $association['joinColumnFieldNames']);
    }

    /**
     * @param array $association
     * @return bool
     */
    public function isColumnOwnerAssociation(array $association)
    {
        if (!isset($association['joinColumnFieldNames'])) {
            return false;
        }

        return true;
    }
}
