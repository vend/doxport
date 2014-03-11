<?php

namespace Doxport\Metadata;

use Doxport\Test\AbstractMockTest;

class DriverTest extends AbstractMockTest
{
    public function testConstructor()
    {
        $instance = new Driver($this->getMockEntityManager());

        $this->assertInstanceOf('Doxport\Metadata\Driver', $instance);
    }

    public function testGetEntityNames()
    {
        $manager  = $this->getMockEntityManager();
        $doctrine = $manager->getConfiguration()->getMetadataDriverImpl();
        $reader   = $doctrine->getReader();

        $doctrine->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(['Doxport\Metadata\DriverTest']));

        $reader->expects($this->any())
            ->method('getClassAnnotations')
            ->will($this->returnValue([]));

        $instance = new Driver($manager);
        $entities = $instance->getEntityNames();
        $this->assertInternalType('array', $entities);
    }
}
