<?php

namespace Doxport\Console;

use Doxport\Exception\Exception;
use Doxport\Test\AbstractTest;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class ConsoleRunnerTest extends AbstractTest
{
    /**
     * @expectedException Exception
     */
    public function testNoEntityManager()
    {
        $runner = new ConsoleRunner();
        $helper = new HelperSet([]);

        $runner->run($helper);
    }

    public function testRun()
    {
        $runner = new ConsoleRunner();
        $helper = new HelperSet([$this->getMockEmHelper()]);

        $application = $this->getMockApplication();

        $application->expects($this->once())
            ->method('run');

        $runner->run($helper, $application);
    }

    /**
     * @return Application
     */
    protected function getMockApplication()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Application')
            ->getMock();

        return $mock;
    }

    protected function getMockEmHelper()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Helper\HelperInterface')
            //->setConstructorArgs([['em' => $this->getMockEntityManager()]])
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('em'));

        return $mock;
    }
}
