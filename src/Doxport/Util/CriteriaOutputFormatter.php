<?php

namespace Doxport\Util;

use Doxport\Criteria;

/**
 * @deprecated
 */
class CriteriaOutputFormatter
{
    /**
     * @param Criteria $criteria
     */
    public function __construct(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param integer $indent
     * @return string
     */
    public function getOutput($indent = 0)
    {
        $children = $this->criteria->getChildren();
        $parent   = $this->criteria->getParent();

        $string = sprintf(
            '%s%s%s %s',
            str_repeat(' ', $indent),
            $parent ? '\\' : '*',
            count($children) ? '-+' : '--',
            str_pad($this->criteria->getEntityName(), 40, ' ', STR_PAD_RIGHT)
        );

        if (($where = $this->criteria->getWhereEq())) {
            $result = [];

            foreach ($where as $column => $value) {
                $result[] = $column . ' = ' . $value;
            }

            $string .= sprintf(' (%s)', implode(', ', $result));
        }

        if (($where = $this->criteria->getWhereEqParent())) {
            $string .= sprintf(' (via %s)', $where);
        }

        $string .= "\n";

        foreach ($children as $child) {
            $formatter = new self($child);
            $string .= $formatter->getOutput($indent + 3);
        }

        return $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getOutput();
    }
}
