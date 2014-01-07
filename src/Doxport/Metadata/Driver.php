<?php

namespace Doxport\Metadata;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\DBAL\Driver\Statement;
use \LogicException;
use Doxport\Exception\UnimplementedException;
use PDO;

class Driver
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Statement
     */
    protected $coveringStatement;

    /**
     * @var Entity[]
     */
    protected $entities = [];

    /**
     * @var MappingDriver
     */
    protected $doctrine;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        if ($association['joinColumns']) {
            foreach ($association['joinColumns'] as $joinColumn) {
                if (isset($joinColumn['nullable']) && !$joinColumn['nullable']) {
                    // nullable is true by default
                    return false;
                }
            }
        }

        return true;  //$this->driver->isNullableColumn($association['sourceEntity'], $association['joinColumnFieldNames']);
    }

    /**
     * @return MappingDriver
     * @throws LogicException
     */
    protected function getDoctrineMetadataDriver()
    {
        if (!isset($this->doctrine)) {
            $this->doctrine = $this->em->getConfiguration()->getMetadataDriverImpl();

            if (!($this->doctrine instanceof AnnotationDriver)) {
                throw new LogicException('Doxport expects Doctrine2 to be using an annotation metadata driver');
            }
        }

        return $this->doctrine;
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     * @throws LogicException
     */
    protected function getAnnotationReader()
    {
        return $this->getDoctrineMetadataDriver()->getReader();
    }

    /**
     * @param $entity
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
            $relevant = array_filter($reader->getPropertyAnnotations($property), function ($value) {
                return implode('\\', array_slice(explode('\\', get_class($value)), 0, -1)) == 'Doxport\\Annotation';
            });

            if (!$relevant) {
                continue;
            }

            $property = new Property($name, true, $meta->getClassMetadata()->getAssociationMapping($property->name));
            $meta->addProperty($property);
        }

        return $this->entities[$entity] = $meta;
    }

    /**
     * @param $sourceEntity
     * @param $joinColumnFieldNames
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Doxport\Exception\UnimplementedException
     * @todo Multiple join columns
     */
    public function isCovered($sourceEntity, $joinColumnFieldNames)
    {
        if (count($joinColumnFieldNames) > 1) {
            throw new UnimplementedException('Cannot yet handle more than one join column');
        }

        $table = $this->getEntityMetadata($sourceEntity)->getClassMetadata()->getTableName();

        if (strpbrk($table, '\\` ') !== false) {
            throw new \InvalidArgumentException('Cannot handle character in table name');
        }

        $column = array_pop($joinColumnFieldNames);

        // No escaping of table name possible, no support in DBAL
        $sql = 'SHOW INDEXES FROM `' . $table . '` WHERE Column_name = ? AND Seq_in_index = 1';

        $result = $this->em->getConnection()->executeQuery($sql, [$column]);

        return $result && $result->rowCount() > 0;
    }

    /**
     * @param array $association
     * @return bool
     */
    public function isCoveredAssociation(array $association)
    {
        return $this->isCovered($association['sourceEntity'], $association['joinColumnFieldNames']);
    }
}
