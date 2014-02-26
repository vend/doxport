<?php

namespace Doxport\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;

/**
 * Entity metadata holder
 */
class Entity
{
    /**
     * @var Property[]
     */
    protected $properties = [];

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $class;

    /**
     * @param DoctrineClassMetadata $class
     */
    public function __construct(DoctrineClassMetadata $class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->class->getName();
    }

    /**
     * @param Property $property
     */
    public function addProperty(Property $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return DoctrineClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->class;
    }

    /**
     * @param string $fieldName
     * @return Property|null
     */
    public function getProperty($fieldName)
    {
        return isset($this->properties[$fieldName]) ? $this->properties[$fieldName] : null;
    }
}
