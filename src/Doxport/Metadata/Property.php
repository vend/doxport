<?php

namespace Doxport\Metadata;

/**
 * Wraps metadata about a particular property of an entity
 */
class Property
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var array
     */
    protected $association;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $annotations
     * @param array $association
     */
    public function __construct($name, array $annotations, array $association)
    {
        $this->name        = $name;
        $this->annotations = $annotations;
        $this->association = $association;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     * @throws \LogicException
     */
    public function getTargetEntity()
    {
        if (!isset($this->association['targetEntity'])) {
            throw new \LogicException('No target entity on association of marked property');
        }

        return $this->association['targetEntity'];
    }

    /**
     * @return array
     */
    public function getJoinColumnFieldNames()
    {
        return isset($this->association['joinColumnFieldNames']) ? $this->association['joinColumnFieldNames'] : [];
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
