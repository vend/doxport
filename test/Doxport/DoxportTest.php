<?php

namespace Doxport;

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
}
