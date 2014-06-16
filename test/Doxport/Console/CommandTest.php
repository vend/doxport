<?php

namespace Doxport\Console;

use Doxport\Console\Base\Command;
use Doxport\Test\AbstractTest;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTest extends AbstractTest
{
    protected function getCommandTester()
    {
        return new CommandTester($this->getCommand());
    }

    /**
     * @return Command
     */
    abstract protected function getCommand();
}
