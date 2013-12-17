<?php

namespace Doxport;

use Doxport\Metadata\Entity;
use Doxport\Util\CriteriaOutputFormatter;

class Criteria
{
    /**
     * Table alias map
     *
     * @var array<string => string>
     */
    protected static $alias = [];

    /**
     * @var Entity
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Criteria
     */
    protected $parent;

    /**
     * @var Criteria[]
     */
    protected $children = [];

    /**
     * @var array
     */
    protected $whereEqParent = [];

    /**
     * @var array
     */
    protected $whereEq = [];

    /**
     * @param Entity $metadata
     * @return void
     */
    public function setEntity(Entity $metadata)
    {
        $this->metadata = $metadata;
        $this->name     = $metadata->getName();
    }

    /**
     * @param Criteria $criteria
     * @param array    $via
     * @return void
     */
    public function attachChild(Criteria $criteria, array $via)
    {
        $this->children[] = $criteria;

        $criteria->setParent($this);
        $criteria->setWhereEqParent($via['fieldName']);
    }

    /**
     * @param string $column
     * @param mixed  $value
     * @return void
     */
    public function addWhereEq($column, $value)
    {
        $this->whereEq[$column] = $value;
    }

    /**
     * @param string $association Field name of the association
     * @return void
     */
    public function setWhereEqParent($association)
    {
        $this->whereEqParent = $association;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        $parts = explode('\\', $this->name);
        return $parts[count($parts) - 1];
    }

    /**
     * @return Criteria[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return array
     */
    public function getWhereEqParent()
    {
        return $this->whereEqParent;
    }

    /**
     * @return array
     */
    public function getWhereEq()
    {
        return $this->whereEq;
    }

    /**
     * @param string $other
     * @return array
     */
    public function getAssociationsTo($other)
    {
        return $this->metadata->getClassMetadata()->getAssociationsByTargetClass($other);
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getQueryAlias()
    {
        $name = $this->getEntityName();

        if (!isset(self::$alias[$name])) {
            $char   = strtolower(strrchr($name, '\\')[1]);
            $simple = $char;
            $index  = 1;

            while (in_array($simple, self::$alias)) {
                $simple = $char . (++$index);
            }

            self::$alias[$name] = $simple;
        }

        return self::$alias[$name];
    }

    /**
     * @param Criteria $criteria
     * @return void
     */
    protected function setParent(Criteria $criteria)
    {
        $this->parent = $criteria;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $formatter = new CriteriaOutputFormatter($this);
        return $formatter->getOutput();
    }
}
