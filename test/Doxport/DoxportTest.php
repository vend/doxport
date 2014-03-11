<?php

namespace Doxport;

class DoxportTest extends AbstractMockTest
{
    public function testConstructor()
    {
        $doxport = new Doxport($this->getMockEntityManager());

        // Defaults if not injected
        $this->assertInstanceOf('Doxport\Metadata\Driver', $doxport->getMetadataDriver());
        $this->assertInstanceOf('Doxport\File\Factory', $doxport->getFileFactory());
    }
}
