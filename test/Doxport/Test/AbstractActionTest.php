<?php

namespace Doxport\Test;

use Doxport\Action\Base\Action;
use Doxport\Doxport;

abstract class AbstractActionTest extends AbstractEntityManagerTest
{
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
        $instance = parent::getDoxport();

        $action = $this->getAction($instance);
        $action->setMetadataDriver($instance->getMetadataDriver());
        $action->setFileFactory($instance->getFileFactory());
        $action->setLogger($instance->getLogger());

        $instance->setAction($action);

        return $instance;
    }

    /**
     * @return Action
     */
    abstract protected function getAction();
}
