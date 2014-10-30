<?php

namespace Doxport\Console;

use Doxport\Console\Base\ActionCommandTest;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommandTest extends ActionCommandTest
{
    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresDataDir()
    {
        $this->getCommandTester()->execute([], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }

    /**
     * @inheritDoc
     */
    protected function getCommand()
    {
        return new ImportCommand();
    }
}
