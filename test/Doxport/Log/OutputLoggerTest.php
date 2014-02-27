<?php

namespace Doxport\Log;

use Doxport\Test;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLoggerTest extends Test
{
    protected function getMockOutputInterface($verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->getMock();

        $mock->expects($this->any())
            ->method('getVerbosity')
            ->will($this->returnValue($verbosity));

        return $mock;
    }

    public function testInterpolate()
    {
        $mock = $this->getMockOutputInterface();

        $mock->expects($this->once())
            ->method('writeln')
            ->with('Some string');

        $logger = new OutputLogger($mock);
        $logger->notice('Some {thing}', ['thing' => 'string']);
    }

    public function testVerbosityQuiet()
    {
        $mock = $this->getMockOutputInterface(OutputInterface::VERBOSITY_QUIET);

        $mock->expects($this->exactly(0))
            ->method('writeln');

        $logger = new OutputLogger($mock);
        $logger->critical('An ignored message');
    }

    public function testVerbosityNormal()
    {
        $mock = $this->getMockOutputInterface(OutputInterface::VERBOSITY_NORMAL);

        $mock->expects($this->exactly(1))
            ->method('writeln');

        $logger = new OutputLogger($mock);
        $logger->info('An ignored message');
        $logger->notice('An outputted message');
    }

    public function testVerbosityVerbose()
    {
        $mock = $this->getMockOutputInterface(OutputInterface::VERBOSITY_VERBOSE);

        $mock->expects($this->exactly(1))
            ->method('writeln');

        $logger = new OutputLogger($mock);
        $logger->info('An outputted message');
        $logger->debug('An ignored message');
    }

    public function testVerbosityVeryVerbose()
    {
        $mock = $this->getMockOutputInterface(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $mock->expects($this->exactly(2))
            ->method('writeln');

        $logger = new OutputLogger($mock);
        $logger->info('An outputted message');
        $logger->debug('An outputted message');
    }
}
