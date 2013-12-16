<?php

namespace Doxport;

use Doxport\Exception\UnimplementedException;
use Doxport\Metadata\Entity;

class Criteria
{
    /**
     * toString support only
     *
     * @var int
     */
    private static $indent = 0;

    /**
     * @var Entity
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $name;

    protected $parent;
    protected $children = [];

    protected $whereEqParent = [];
    protected $whereEq = [];

    public function setEntity(Entity $metadata)
    {
        $this->metadata = $metadata;
        $this->name     = $metadata->getName();
    }

    public function getEntityName()
    {
        return $this->name;
    }

    public function addWhereEq($column, $value)
    {
        $this->whereEq[] = [$column, $value];
    }

    /**
     * @param string $other
     * @return array
     */
    public function getAssociationsTo($other)
    {
        return $this->metadata->getClassMetadata()->getAssociationsByTargetClass($other);
    }

    public function getPropertiesToFollow()
    {
        throw new UnimplementedException('Not used?');

        if (!$this->metadata) {
            throw new \LogicException('No entity set');
        }

        return $this->metadata->getProperties();
    }

    /**
     * @param Criteria $criteria
     */
    protected function setParent(Criteria $criteria)
    {
        $this->parent = $criteria;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param array $columnMap
     */
    public function setWhereEqParent($association)
    {
        $this->whereEqParent[] = $association;
    }

    /**
     * @param Criteria $criteria
     * @param array    $via
     */
    public function attachChild(Criteria $criteria, array $via)
    {
        $this->children[] = $criteria;

        $criteria->setParent($this);
        $criteria->setWhereEqParent($via['fieldName']);
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        $string = str_repeat(' ', self::$indent);
        $string .= '+--' . $this->metadata->getName();

        if ($this->whereEq) {
            $string .= ' (';

            $string .= implode(', ', array_map(function ($v) {
                return $v[0] . ' = ' . $v[1];
            }, $this->whereEq));

            $string .= ')';
        }

        if ($this->whereEqParent) {
            $string .= ' (';

            $result = [];
            foreach ($this->whereEqParent as $local => $foreign) {
                $result[] = $local . ' = %.' . $foreign;
            }

            $string .= implode(', ', $result);

            $string .= ')';
        }

        $string .= "\n";

        self::$indent += 2;

        foreach ($this->children as $child) {
            $string .= (string)$child;
        }

        self::$indent -= 2;

        return $string;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getWhereEqParent()
    {
        return $this->whereEqParent;
    }
}
