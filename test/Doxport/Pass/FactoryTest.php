<?php

namespace Doxport\Pass;

use Doxport\Action\Base\Action;
use Doxport\EntityGraph;
use Doxport\File\Factory as FileFactory;
use Doxport\Metadata\Driver;
use Doxport\Test\AbstractTest;

class FactoryTest extends AbstractTest
{
    /**
     * @expectedException Doxport\Exception\InvalidArgumentException
     */
    public function testInvalidPassType()
    {
        $sut = $this->getSut();
        $sut->get('invalid');
    }

    protected function getSut()
    {
        return new Factory(
            $this->getMockDriver(),
            $this->getMockEntityGraph(),
            $this->getMockAction(),
            $this->getMockFileFactory()
        );
    }

    /**
     * @return Driver
     */
    protected function getMockDriver()
    {
        return $this->getMockBuilder('Doxport\Metadata\Driver')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return EntityGraph
     */
    protected function getMockEntityGraph()
    {
        return $this->getMockBuilder('Doxport\EntityGraph')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return Action
     */
    protected function getMockAction()
    {
        return $this->getMockBuilder('Doxport\Action\Base\Action')
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass();
    }

    /**
     * @return FileFactory
     */
    protected function getMockFileFactory()
    {
        return $this->getMockBuilder('Doxport\File\Factory')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
