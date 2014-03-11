<?php

namespace Doxport;

abstract class AbstractMockTest extends AbstractTest
{
    protected function getMockMetadataDriverImpl()
    {
        $driver = $this->getMockBuilder('Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->getMock();

        return $driver;
    }

    protected function getMockConfiguration()
    {
        $configuration = $this->getMockBuilder('Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        $configuration->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($this->getMockMetadataDriverImpl()));

        return $configuration;
    }

    protected function getMockEntityManager()
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->getMockConfiguration()));

        return $manager;
    }
}
