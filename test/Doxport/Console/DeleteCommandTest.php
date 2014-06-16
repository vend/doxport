<?php

namespace Doxport\Console;

use Doxport\Console\Base\QueryActionCommandTest;

class DeleteCommandTest extends QueryActionCommandTest
{
    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresEntity()
    {
        $this->getCommandTester()->execute([
            '--verbose' => true,
            'column'    => 'foo',
            'value'     => 'bar'
        ]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresColumn()
    {
        $this->getCommandTester()->execute([
            '--verbose' => true,
            'entity'    => 'foo',
            'value'     => 'bar'
        ]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRequiresValue()
    {
        $this->getCommandTester()->execute([
            '--verbose' => true,
            'entity'    => 'foo',
            'column'    => 'bar'
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getCommand()
    {
        return new DeleteCommand();
    }
}
