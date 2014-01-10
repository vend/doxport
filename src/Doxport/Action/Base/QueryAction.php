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
        $walk = $this->getJoinWalk($path);
        $this->processQuery($walk);
    }

    public function processSelfJoin(Walk $path, array $association)
    {
        $walk = $this->getJoinWalk($path);

        // nope
      //  foreach ($association['joinColumns'] as $column) {
      //      $walk->whereTargetFieldNull($column['name']);
      //  }

        // @todo do an actual self-join

        $walk->whereTargetFieldNull($association['fieldName']); // Do an actual self-join

        $this->processQuery($walk);
    }

    protected function getJoinWalk(Walk $path)
    {
        $walk = new JoinWalk(
            $path,
            $this->em->createQueryBuilder(),
            new AliasGenerator()
        );

        foreach ($this->rootCriteria as $criteria) {
            $walk->whereRootFieldEq($criteria['column'], $criteria['value']);
        }

        return $walk;
    }

    /**
     * @param \Doxport\Doctrine\JoinWalk|\Fhaculty\Graph\Walk $walk
     * @return mixed
     */
    abstract protected function processQuery(JoinWalk $walk);
}
