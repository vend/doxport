<?php

namespace Doxport\Metadata;

use Doxport\Annotation\Exclude;
use Doxport\Test\AbstractTest;
use LogicException;

class PropertyTest extends AbstractTest
{
    public function testHasAnnotation()
    {
        $property = new Property('something', [new Exclude()], []);

        $this->assertTrue($property->hasAnnotation('Doxport\Annotation\Exclude'));
        $this->assertFalse($property->hasAnnotation('Doxport\Annotation\Clear'));
    }

    public function testGetTargetEntity()
    {
        $target = uniqid();

        $property = new Property('something', [new Exclude()], [
            'targetEntity' => $target
        ]);

        $this->assertEquals($target, $property->getTargetEntity());
    }

    /**
     * @expectedException LogicException
     */
    public function testNoTargetEntity()
    {
        $property = new Property('something', [], []);
        $property->getTargetEntity();
    }
}
