<?php

namespace Doxport\Test;

abstract class AbstractQueryActionTest extends AbstractEntityManagerTest
{
    public function testQueryAction()
    {
        $doxport = $this->getDoxport();

        $doxport->getFileFactory()->createPath();

        $pass = $doxport->getConstraintPass();
        $this->assertInstanceOf('Doxport\Pass\ConstraintPass', $pass);

        $vertices = $pass->run();
        $this->assertInstanceOf('Fhaculty\Graph\Set\Vertices', $vertices);

        $pass = $doxport->getClearPass($vertices);
        $this->assertInstanceOf('Doxport\Pass\ClearPass', $pass);

        $pass->run();

        $pass = $doxport->getJoinPass($vertices);
        $this->assertInstanceOf('Doxport\Pass\JoinPass', $pass);

        $pass->run();
    }
}
