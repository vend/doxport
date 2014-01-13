<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doxport\Doctrine\AliasGenerator;
use Doxport\Doctrine\JoinWalk;
use Doxport\Util\SimpleObjectSerializer;
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
     * @var array
     */
    protected $processedSelfJoins = [];

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

    /**
     * @param Walk $path
     * @param array $association
     */
    public function processSelfJoin(Walk $path, array $association)
    {
        $walk = $this->getJoinWalk($path);

        if ($association['type'] == ClassMetadata::ONE_TO_MANY) {
            // Skipping because inverse of one that's already processed
            return;
        }

        $walk->addSelfJoinNull($association['inversedBy'], $association['sourceToTargetKeyColumns']);

        // One level of self-join
        $this->processQuery($walk);

        // Two levels of self-join
        $this->processQuery($walk);
    }

    /**
     * @param Walk $path
     * @return JoinWalk
     */
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
     * @param object $entity
     * @todo better name, not really serialization
     * @return array
     */
    protected function serialize($entity)
    {
        if (method_exists($entity, '__sleep')) {
            return $entity->__sleep();
        }

        $serializer = new SimpleObjectSerializer($this->em);
        return $serializer->serialize($entity);
    }

    /**
     * @param \Doxport\Doctrine\JoinWalk|\Fhaculty\Graph\Walk $walk
     * @return mixed
     */
    abstract protected function processQuery(JoinWalk $walk);
}
