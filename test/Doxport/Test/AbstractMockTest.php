<?php

namespace Doxport\Test;

use \AnnotationReader;
use Doxport\Test\AbstractTest;

abstract class AbstractMockTest extends AbstractTest
{
    protected function getMockLogger()
    {
        $logger = $this->getMockBuilder('Doxport\Log\Logger')
            ->getMockForAbstractClass();

        return $logger;
    }

    protected function getMockReader()
    {
        $reader = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->getMockForAbstractClass();

        return $reader;
    }

    protected function getMockMetadataDriverImpl()
    {
        $driver = $this->getMockBuilder('Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->getMock();

        $driver->expects($this->any())
            ->method('getReader')
            ->will($this->returnValue($this->getMockReader()));

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
