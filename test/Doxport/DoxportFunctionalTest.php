<?php

namespace Doxport;

use Doxport\Action\Base\Action;
use Doxport\Action\Export;
use Doxport\Test\AbstractEntityManagerTest;

class DoxportFunctionalTest extends AbstractEntityManagerTest
{
    /**
     * @var string
     */
    protected static $fixture = 'Library';

    /**
     * @expectedException \LogicException
     */
    public function testNoEntityType()
    {
        $instance = new Doxport($this->em);
        $instance->setLogger($this->getMockLogger());

        $instance->getConstraintPass();
    }

    /**
     * @expectedException \LogicException
     */
    public function testNoAction()
    {
        $instance = new Doxport($this->em);
        $instance->setLogger($this->getMockLogger());
        $instance->setEntity('Doxport\Test\Fixtures\Bookstore\Entities\Book');
        $instance->setOption('root', true);

        $instance->getConstraintPass();
    }

    public function testGetConstraintPass()
    {
        $instance = $this->getDoxport();

        $pass = $instance->getConstraintPass();
        $this->assertInstanceOf('Doxport\Pass\ConstraintPass', $pass);

        $vertices = $pass->run();
        $this->assertInstanceOf('Fhaculty\Graph\Set\Vertices', $vertices);

        return $vertices;
    }

    /**
     * @depends testGetConstraintPass
     */
    public function testGetClearPass($vertices)
    {
        $instance = $this->getDoxport();

        $pass = $instance->getClearPass($vertices);
        $this->assertInstanceOf('Doxport\Pass\ClearPass', $pass);

        $pass->run();
    }

    /**
     * @depends testGetConstraintPass
     */
    public function testGetJoinPass($vertices)
    {
        $instance = $this->getDoxport();

        $pass = $instance->getJoinPass($vertices);
        $this->assertInstanceOf('Doxport\Pass\JoinPass', $pass);

        $pass->run();
    }

    /**
     * @return Doxport
     */
    protected function getDoxport()
    {
        $instance = new Doxport($this->em);
        $instance->setLogger($this->getMockLogger());
        $instance->setEntity('Doxport\Test\Fixtures\Bookstore\Entities\Book');
        $instance->setOption('root', true);
        $instance->setAction($this->getAction($instance));

        return $instance;
    }

    /**
     * @param Doxport $doxport
     * @return Action
     */
    protected function getAction(Doxport $doxport)
    {
        $action = new Export($this->em);
        $action->setMetadataDriver($doxport->getMetadataDriver());
        $action->setFileFactory($doxport->getFileFactory());
        $action->setLogger($doxport->getLogger());

        return $action;
    }
}
