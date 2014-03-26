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
     * @var string
     */
    protected static $fixture = 'Library';

    /**
     * @var array<string>
     */
    protected static $fixtureTypes = [
        'Shop'    => 'yaml',
        'Library' => 'annotation'
    ];

    /**
     * @return string
     */
    protected static function getFixtureDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR
        . self::$fixture;
    }

    /**
     * @return string
     */
    protected static function getEntityDirectory()
    {
        return self::getFixtureDirectory() . DIRECTORY_SEPARATOR
        . 'Entities';
    }

    /**
     * @return string
     */
    protected static function getExportedDirectory()
    {
        return self::getFixtureDirectory() . DIRECTORY_SEPARATOR
        . 'Exported';
    }

    /**
     * @return string
     */
    protected static function getFixtureFile()
    {
        return self::getFixtureDirectory() . DIRECTORY_SEPARATOR
        . 'fixtures.php';
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        if (!is_dir(self::$root)) {
            mkdir(self::$root);
        }
    }

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
