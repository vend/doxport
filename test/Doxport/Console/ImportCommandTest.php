<?php

namespace Doxport\Console;

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
     * @return Command
     */
    protected function getCommand()
    {
        return new ImportCommand();
    }
}
