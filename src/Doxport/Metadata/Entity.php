<?php

namespace Doxport\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;

class Entity
{
    protected $properties = [];
    protected $class;

    public function __construct(DoctrineClassMetadata $class)
    {
        $this->class = $class;
    }

    public function getName()
    {
        return $this->class->getName();
    }

    public function addProperty(Property $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getClassMetadata()
    {
        return $this->class;
    }

    public function getProperty($fieldName)
    {
        return isset($this->properties[$fieldName]) ? $this->properties[$fieldName] : null;
    }
}
