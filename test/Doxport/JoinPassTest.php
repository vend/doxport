<?php

namespace Doxport;

use Doxport\Test\MockCriteriaFactory;

class JoinPassTest extends Test
{
    public $data = [
        'A' => ['B', 'C'],
        'B' => ['C'],
        'C' => []
    ];

    public function testSecondaryOrder()
    {
        reset($this->data);

        $schema = new Schema($this->getMetadataDriver(), new MockCriteriaFactory($this->data));
        $schema->setRoot(key($this->data));

        $pass = new JoinPass($this->getMetadataDriver(), $schema);
        $pass->reduce();
    }

    protected function getMetadataDriver()
    {
        $driver = $this->getMockBuilder('Doxport\Metadata\Driver')
            ->disableOriginalConstructor()
            ->getMock();

        $driver->expects($this->any())
            ->method('getEntityNames')
            ->will($this->returnValue(array_keys($this->data)));

        $driver->expects($this->any())
            ->method('getEntityMetadata')
            ->will($this->returnCallback(function ($name) {
                return $this->getEntityMetadata($name);
            }));

        $driver->expects($this->any())
            ->method('getAssociationsTo')
            ->will($this->returnCallback(function ($target) {
                $b = 1;
            }));

        return $driver;
    }

    protected function getEntityMetadata($name)
    {
        $entity = $this->getMockBuilder('Doxport\Metadata\Entity')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $entity;
    }


}
