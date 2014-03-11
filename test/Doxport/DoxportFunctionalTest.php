<?php

namespace Doxport;

use Doxport\Action\Export;
use Doxport\Test\AbstractEntityManagerTest;

class DoxportFunctionalTest extends AbstractEntityManagerTest
{
    protected $fixtures = 'Bookstore';

    /**
     * @expectedException \LogicException
     */
    public function testNoEntityType()
    {
        $em = $this->getEntityManager();

        $instance = new Doxport($em);
        $instance->setLogger($this->getMockLogger());

        $instance->getConstraintPass();
    }

    /**
     * @expectedException \LogicException
     */
    public function testNoAction()
    {
        $em = $this->getEntityManager();

        $instance = new Doxport($em);
        $instance->setLogger($this->getMockLogger());
        $instance->setEntity('Doxport\Test\Fixtures\Bookstore\Entities\Book');
        $instance->setOption('root', true);

        $instance->getConstraintPass();
    }

    public function testGetConstraintPass()
    {
        $em = $this->getEntityManager();

        $instance = new Doxport($em);
        $instance->setLogger($this->getMockLogger());
        $instance->setEntity('Doxport\Test\Fixtures\Bookstore\Entities\Book');
        $instance->setAction(new Export($em));
        $instance->setOption('root', true);

        $instance->getConstraintPass();
    }
}
