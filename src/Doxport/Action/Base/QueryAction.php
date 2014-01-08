<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doxport\Doctrine\AliasGenerator;
use Doxport\Doctrine\JoinWalk;
use Fhaculty\Graph\Walk;

abstract class QueryAction extends Action
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $rootCriteria = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $column
     * @param mixed  $value
     * @return void
     */
    public function addRootCriteria($column, $value)
    {
        $this->rootCriteria[] = [
            'column' => $column,
            'value'  => $value
        ];
    }

    /**
     * @param Walk $path
     * @return void
     */
    public function process(Walk $path)
    {
        $walk = new JoinWalk(
            $path,
            $this->em->createQueryBuilder(),
            new AliasGenerator()
        );

        foreach ($this->rootCriteria as $criteria) {
            $walk->applyRootCriteria($criteria['column'], $criteria['value']);
        }

        $this->processQuery($walk);
    }

    /**
     * @param \Doxport\Doctrine\JoinWalk|\Fhaculty\Graph\Walk $walk
     * @return mixed
     */
    abstract protected function processQuery(JoinWalk $walk);
}
