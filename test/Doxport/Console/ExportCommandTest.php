<?php

namespace Doxport\Console;

class ExportCommandTest extends QueryActionCommandTest
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

    protected function getCommand()
    {
        return new ExportCommand();
    }
}
