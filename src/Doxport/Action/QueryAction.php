<?php

namespace Doxport\Action;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doxport\Util\QueryAliases;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;

abstract class QueryAction extends Action
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var QueryAliases
     */
    protected $aliases;

    /**
     * @var array
     */
    protected $rootCriteria = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, QueryAliases $aliases)
    {
        $this->em      = $em;
        $this->aliases = $aliases;
    }

    public function addRootCriteria($column, $value)
    {
        $this->rootCriteria[] = [
            'column' => $column,
            'value'  => $value
        ];
    }

    public function process(Vertex $vertex, Walk $walk)
    {
        $target = $vertex->getId();

        $this->logger->notice('Collecting criteria to produce SELECT for ' . $target);

        $builder = new QueryBuilder($this->em);
        $builder->select($alias = $this->aliases->get($target));
        $builder->from($target, $alias);




        $query = $builder->getQuery();
        $sql = $query->getSQL();

        $a = 1;
    }

    protected function getSelectQuery(Walk $walk)
    {



        $builder->from($entity = $criteria->getEntityName(), $criteria->getQueryAlias());

        $this->applyRecursive($criteria, $builder);

        // TODO: use correct 'id' columns
//        $crawl = $criteria->getQueryAlias() . '.id';
//
//        $builder->andWhere($builder->expr()->gt(
//            $crawl,
//            ':crawl'
//        ));
//
//        $builder->setParameter('crawl', '');
//
//        $builder->orderBy($builder->expr()->asc(
//            $crawl
//        ));
    }

    /**
     * @deprecated
     * @param Criteria     $criteria
     * @param QueryBuilder $builder
     * @return void
     */
    protected function applyRecursive(Criteria $criteria, QueryBuilder $builder)
    {
        if (($eq = $criteria->getWhereEq())) {
            foreach ($criteria->getWhereEq() as $column => $value) {
                $qualified = $criteria->getQueryAlias() . '.' . $column;
                $param     = ':' . $criteria->getQueryAlias() . $column;

                $builder->andWhere(
                    $builder->expr()->eq(
                        $qualified,
                        $param
                    )
                );

                $builder->setParameter(
                    $param,
                    $value
                );
            }
        }

        if (($parent = $criteria->getParent())) {
            if (($eqp = $criteria->getWhereEqParent())) {
                $builder->innerJoin(
                    $criteria->getQueryAlias() . '.' . $eqp,
                    $parent->getQueryAlias()
                );
            }

            $this->applyRecursive($parent, $builder);
        }
    }
}
