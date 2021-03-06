<?php

namespace Doxport\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Walk;

class JoinWalk
{
    /**
     * @var Walk
     */
    protected $walk;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $builder;

    /**
     * @var AliasGenerator
     */
    protected $aliases;

    /**
     * @param Walk           $walk
     * @param QueryBuilder   $builder
     * @param AliasGenerator $aliases
     */
    public function __construct(
        Walk $walk,
        QueryBuilder $builder,
        AliasGenerator $aliases
    ) {
        $this->walk = $walk;
        $this->builder = $builder;
        $this->aliases = $aliases;

        $this->selectTarget();
        $this->addJoins();
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->builder->getQuery();
    }

    /**
     * @return void
     */
    protected function selectTarget()
    {
        $alias = $this->aliases->get($this->getTargetId());

        $this->builder->select($alias);
        $this->builder->from($this->getTargetId(), $alias);
    }

    /**
     * @var Directed $edge
     * @return void
     */
    protected function addJoins()
    {
        foreach ($this->walk->getEdges() as $edge) {
            $relation = $edge->getLayoutAttribute('label');

            $this->builder->innerJoin(
                $this->aliases->get($edge->getVertexStart()->getId()) . '.' . $relation,
                $this->aliases->get($edge->getVertexEnd()->getId())
            );
        }
    }

    /**
     * @return string
     */
    protected function getRootId()
    {
        return $this->walk->getVertexTarget()->getId();
    }

    /**
     * @return string
     */
    public function getTargetId()
    {
        return $this->walk->getVertexSource()->getId();
    }

    /**
     * @param string $column
     * @param mixed  $value
     * @return void
     */
    public function whereRootFieldEq($column, $value)
    {
        $qualified = $this->aliases->get($this->getRootId()) . '.' . $column;
        $param     = ':' . $this->aliases->get($this->getRootId()) . $column;

        $this->builder->andWhere(
            $this->builder->expr()->eq(
                $qualified,
                $param
            )
        );

        $this->builder->setParameter(
            $param,
            $value
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [];

        /* @var Directed $edge */
        foreach ($this->walk->getEdges() as $edge) {
            $from = $this->getEntityClassName($edge->getVertexStart()->getId());
            $to   = $this->getEntityClassName($edge->getVertexEnd()->getId());

            $parts[] = sprintf(
                '  %s %s %s',
                str_pad($from, 20, ' ', STR_PAD_LEFT),
                str_pad($edge->getLayoutAttribute('label'), 15, '-', STR_PAD_BOTH) . '>',
                $to
            );
        }

        return implode("\n", $parts);
    }

    /**
     * @param string $class
     * @return string
     */
    protected function getEntityClassName($class)
    {
        $parts = explode('\\', $class);
        return $parts[count($parts) - 1];
    }
}
