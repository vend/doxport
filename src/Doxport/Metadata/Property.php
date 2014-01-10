<?php

namespace Doxport\Metadata;

class Property
{
    public function __construct($name, array $annotations, array $association)
    {
        $this->name = $name;
        $this->annotations = $annotations;
        $this->association = $association;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTargetEntity()
    {
        if (!isset($this->association['targetEntity'])) {
            throw new \LogicException('No target entity on association of marked property');
        }
        return $this->association['targetEntity'];
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function hasAnnotation($class)
    {
        foreach ($this->annotations as $annotation) {
            if ($annotation instanceof $class) {
                return true;
            }
        }
    }
}
