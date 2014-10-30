<?php

namespace Doxport\Console;

use Doxport\Console\Base\QueryActionCommandTest;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommandTest extends QueryActionCommandTest
{
    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresEntity()
    {
        $this->getCommandTester()->execute([
            'column'    => 'foo',
            'value'     => 'bar'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresColumn()
    {
        $this->getCommandTester()->execute([
            'entity'    => 'foo',
            'value'     => 'bar'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresValue()
    {
        $this->getCommandTester()->execute([
            'entity'    => 'foo',
            'column'    => 'bar'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }

    /**
     * @inheritDoc
     */
    protected function getCommand()
    {
        return new DeleteCommand();
    }
}
