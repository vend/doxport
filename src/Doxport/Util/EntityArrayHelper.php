<?php

namespace Doxport\Util;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;

class EntityArrayHelper
{
    const SERIALIZED_KEY = '__serialized';

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
     * @param object $entity The entity to convert
     * @param array  $fields Optional filtering by field names
     * @return array
     */
    public function toArray($entity, array $fields = [])
    {
        $data     = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
        $result = [];

        if ($fields) {
            $data = array_intersect_key($data, array_flip($fields));
        }

        foreach ($data as $field => $value) {
            if ($value instanceof Proxy || $value instanceof Collection) {
                continue;
            }

            if (is_object($value)) {
                if (!isset($result[self::SERIALIZED_KEY])) {
                    $result[self::SERIALIZED_KEY] = [];
                }

                $result[self::SERIALIZED_KEY][] = $field;
                $value = serialize($value);
            }

            $result[$field] = $value;
        }

        return $result;
    }

    /**
     * @param string $entityName
     * @param array  $values
     * @return object Detached Doctrine2 entity instance
     */
    public function toEntity($entityName, $values)
    {
        if (!empty($values[self::SERIALIZED_KEY])) {
            foreach ($values[self::SERIALIZED_KEY] as $field) {
                $values[$field] = unserialize($values[$field]);
            }
        }

        $unit = $this->em->getUnitOfWork();
        $entity = $unit->createEntity($entityName, $values);
        $this->em->detach($entity);
        return $entity;
    }
}
