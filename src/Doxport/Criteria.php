<?php

namespace Doxport;

use Doxport\Metadata\Entity;

class Criteria
{
    /**
     * toString support only
     *
     * @var int
     */
    private static $indent = 0;

    protected $metadata;

    protected $parent;
    protected $children = [];
    protected $whereEq = [];

    public function setEntity(Entity $metadata)
    {
        $this->metadata = $metadata;
    }

    public function addWhereEq($column, $value)
    {
        $this->whereEq[] = [$column, $value];
    }

    public function getPropertiesToFollow()
    {
        if (!$this->metadata) {
            throw new \LogicException('No entity set');
        }
        return $this->metadata->getProperties();
    }

    /**
     * @param Criteria $criteria
     */
    public function setParent(Criteria $criteria)
    {
        $this->parent = $criteria;

        // When we set a new parent, we update the current criteria based on
        // the
    }

    public function attachChild(Criteria $criteria)
    {
        $this->children[] = $criteria;
    }

    public function __toString()
    {
        $string = str_repeat(' ', self::$indent);
        $string .= '+--' . $this->metadata->getName();

        if ($this->whereEq) {
            $string .= ' (';
            $string .= implode(', ', array_map(function ($v) {
                return $v[0] . '=' . $v[1];
            }, $this->whereEq));
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
}
