<?php

namespace Doxport\Util;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;

/**
 * @todo Not actually a serializer, more a toArray helper
 */
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
        $unit     = $this->em->getUnitOfWork();

        $result = [];
        foreach ($unit->getOriginalEntityData($entity) as $field => $value) {
            if (!$metadata->hasField($field)) {
                continue;
            }

            if ($metadata->hasAssociation($field)) {
                continue;
            }

            if (is_object($value)) {
                $type  = Type::getType($metadata->getTypeOfField($field));
                $value = $type->convertToDatabaseValue(
                    $value,
                    $this->em->getConnection()->getDatabasePlatform()
                );
            }

            $result[$metadata->getColumnName($field)] = $value;
        }

        return $result;
    }
}
