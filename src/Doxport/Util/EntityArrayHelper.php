<?php

namespace Doxport\Util;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;

class EntityArrayHelper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \stdClass $entity
     * @return array
     */
    public function toArray($entity)
    {
        $metadata = $this->em->getClassMetadata(get_class($entity));
        $data     = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
        $platform = $this->em->getConnection()->getDatabasePlatform();

        $result = [];
        foreach ($data as $field => $value) {
            if ($value instanceof Proxy || $value instanceof Collection) {
                continue;
            }

            if (is_object($value)) {
                try {
                    $field_type = $metadata->getTypeOfField($field);
                    $type = Type::getType($field_type);
                } catch (DBALException $e) {
                    throw $e;
                }

                $value = $type->convertToDatabaseValue($value, $platform);
            }

            $result[$field] = $value;
        }

        return $result;
    }

    /**
     * @param string $entityName
     * @param array $values
     * @return object Detached Doctrine2 entity instance
     */
    public function toEntity($entityName, $values)
    {
        $unit = $this->em->getUnitOfWork();
        $entity = $unit->createEntity($entityName, $values);
        $this->em->detach($entity);
        return $entity;
    }
}
