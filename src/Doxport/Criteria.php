<?php

namespace Doxport;

use Doxport\Metadata\Entity;

class Criteria
{
    private static $indent = 0;

    protected $metadata;

    protected $parent;
    protected $children = [];

    public function __construct(Entity $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getPropertiesToFollow()
    {
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

    public function attach(Criteria $criteria)
    {
        $this->children[] = $criteria;
    }

    public function __toString()
    {
        $string = str_repeat(' ', self::$indent);
        $string .= '+--' . $this->metadata->getName() . "\n";

        self::$indent += 2;

        foreach ($this->children as $child) {
            $string .= (string)$child;
        }

        self::$indent -= 2;

        return $string;
    }
}
