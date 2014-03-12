<?php

namespace Doxport\Test;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doxport\Doxport;
use LogicException;

abstract class AbstractEntityManagerTest extends AbstractTest
{
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
     * @var ArrayCache
     */
    protected static $cache;

    /**
     * @var Configuration
     */
    protected static $config;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @return array<string>
     */
    protected static function getConnectionOptions()
    {
        return [
            'driver' => 'pdo_sqlite',
            'path'   => self::$root . DIRECTORY_SEPARATOR . 'database.sqlite'
        ];
    }

    /**
     * @return Configuration
     * @throws \LogicException
     */
    protected static function getConfig()
    {
        if (!self::$config) {
            if (!self::$fixtureTypes[self::$fixture]) {
                throw new LogicException('Unknown fixture type for fixture: ' . self::$fixture);
            }

            $type     = self::$fixtureTypes[self::$fixture];
            $cache    = self::getCache();
            $devMode  = true;
            $proxyDir = 'build/tmp/proxies';

            switch ($type) {
                case 'annotation':
                    self::$config = Setup::createAnnotationMetadataConfiguration(
                        [
                            self::getEntityDirectory()
                        ],
                        $devMode,
                        $proxyDir,
                        $cache,
                        false
                    );
                    break;
                case 'yaml':
                    self::$config = Setup::createYAMLMetadataConfiguration(
                        [
                            self::getFixtureDirectory()
                        ],
                        $devMode,
                        $proxyDir,
                        $cache
                    );
                    break;
                default:
                    throw new LogicException('Invalid fixture type for fixture');
            }
        }

        return self::$config;
    }

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
    protected static function getFixtureFile()
    {
        return self::getFixtureDirectory() . DIRECTORY_SEPARATOR
            . 'fixtures.php';
    }

    /**
     * @return ArrayCache
     */
    protected static function getCache()
    {
        return new ArrayCache();
    }

    /**
     * @return EntityManager
     */
    protected static function getEntityManager()
    {
        return EntityManager::create(self::getConnectionOptions(), self::getConfig());
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $em = self::getEntityManager();

        $metadata = array_map(function ($class) use ($em) {
            return $em->getClassMetadata($class);
        }, $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames());

        // Create the schema
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        $tool->createSchema($metadata);

        // Load fixtures if there are any
        if (is_readable(self::getFixtureFile())) {
            include self::getFixtureFile();
        }
    }

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->em = self::getEntityManager();
    }

    /**
     * @return Doxport
     */
    protected function getDoxport()
    {
        $instance = new Doxport($this->em);
        $instance->setLogger($this->getMockLogger());

        $factory = $instance->getFileFactory();
        $factory->setPath(self::$root);
        $factory->join(uniqid(time(), true));
        $factory->createPath();

        return $instance;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->em = null;
    }
}
