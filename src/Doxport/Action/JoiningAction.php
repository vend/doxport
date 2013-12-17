<?php

namespace Doxport\Action;

use Doctrine\ORM\QueryBuilder;
use Doxport\Criteria;

trait JoiningAction
{
    /**
     * @param Criteria     $criteria
     * @param QueryBuilder $builder
     * @return void
     */
    protected function apply(Criteria $criteria, QueryBuilder $builder)
    {
        $builder->select($criteria->getQueryAlias());
        $builder->from($criteria->getEntityName(), $criteria->getQueryAlias());

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
