<?php

namespace Doxport;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $driver = $this->getMockDriver();

        $driver->expects($this->any())
            ->method('getEntityNames')
            ->will($this->returnValue([]));

        $instance = new Schema($driver);
        $this->assertInstanceOf('Doxport\Schema', $instance);
    }

    protected function getMockDriver()
    {
        $mock = $this->getMockBuilder('Doxport\Metadata\Driver')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
