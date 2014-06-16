<?php

namespace Doxport\Console;

use Doxport\Console\Base\ActionCommandTest;

class ImportCommandTest extends ActionCommandTest
{
    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresDataDir()
    {
        $this->getCommandTester()->execute([
            '--verbose' => true
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getCommand()
    {
        return new ImportCommand();
    }
}
