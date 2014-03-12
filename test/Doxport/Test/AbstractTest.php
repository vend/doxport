<?php

namespace Doxport\Test;

use Doxport\Log\Logger;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $root = 'build/tmp';

    /**
     * @return Logger
     */
    protected function getMockLogger()
    {
        $logger = $this->getMockBuilder('Doxport\Log\Logger')
            ->getMockForAbstractClass();

        return $logger;
    }
}
