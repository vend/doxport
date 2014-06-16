<?php

namespace Doxport\Console;

use Doxport\Test\AbstractTest;
use Doxport\Exception\Exception;
use Symfony\Component\Console\Helper\HelperSet;

class ConsoleRunnerTest extends AbstractTest
{
    /**
     * @expectedException Exception
     */
    public function testNoEntityManager()
    {
        $helper = new HelperSet([]);

        $runner = new ConsoleRunner();
        $runner->run($helper);
    }
}
