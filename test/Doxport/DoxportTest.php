<?php

namespace Doxport;

use Doxport\Action\Delete;
use Doxport\Exception\Exception;
use Doxport\Test\AbstractMockTest;

class DoxportTest extends AbstractMockTest
{
    public function testConstructor()
    {
        $manager = $this->getMockEntityManager();
        $doxport = new Doxport($manager);

        // Defaults if not injected
        $this->assertInstanceOf('Doxport\Metadata\Driver', $doxport->getMetadataDriver());
        $this->assertInstanceOf('Doxport\File\Factory', $doxport->getFileFactory());

        // Same instance
        $this->assertEquals($manager, $doxport->getEntityManager());
    }

    public function testSetOptions()
    {
        $doxport = new Doxport($this->getMockEntityManager());
        $doxport->setOptions([
            'root'  => true,
            'image' => false
        ]);
    }

    public function testSetOption()
    {
        $doxport = new Doxport($this->getMockEntityManager());
        $doxport->setOption('root', true);
    }

    public function testSetLogger()
    {
        $doxport = new Doxport($this->getMockEntityManager());
        $doxport->setLogger($this->getMockLogger());
    }

    /**
     * @expectedException Exception
     */
    public function testNoEntityType()
    {
        $instance = new Doxport($this->getMockEntityManager());
        $instance->setLogger($this->getMockLogger());
        $instance->setAction(new Delete($this->getMockEntityManager(), []));

        $instance->getConstraintPass();
    }

    /**
     * @expectedException Exception
     */
    public function testNoAction()
    {
        $instance = new Doxport($this->getMockEntityManager());
        $instance->setLogger($this->getMockLogger());
        $instance->setEntity('Doxport\Test\Fixtures\Library\Entities\Book');
        $instance->setOption('root', true);

        $instance->getConstraintPass();
    }
}
