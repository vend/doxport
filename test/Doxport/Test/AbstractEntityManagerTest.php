<?php

namespace Doxport\Test;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doxport\Action\Base\Action;
use Doxport\Doxport;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractEntityManagerTest extends AbstractTest
{
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
     * @var Doxport
     */
    protected $doxport;

    /**
     * Whether to delete generated files at tearDown
     *
     * @var boolean
     */
    protected $cleanup = true;

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

        self::loadFixtures();
    }

    /**
     * Loads the fixtures
     */
    protected static function loadFixtures()
    {
        // Load fixtures if there are any
        if (is_readable(self::getFixtureFile())) {
            // Available to fixture file variables:
            $em = self::getEntityManager();

            include self::getFixtureFile();
        }
    }

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->em      = self::getEntityManager();
        $this->doxport = $this->getDoxport();
    }

    /**
     * @return Doxport
     */
    protected function getDoxport()
    {
        if (empty($this->doxport)) {
            $instance = new Doxport($this->em);
            $instance->setLogger($this->getMockLogger());

            $instance->getFileFactory()
                ->setPath(self::$root)
                ->join(uniqid(get_class($this), true));

            $action = $this->getAction($instance);
            $action->setMetadataDriver($instance->getMetadataDriver());
            $action->setFileFactory($instance->getFileFactory());
            $action->setLogger($instance->getLogger());

            $instance->setAction($action);

            $this->doxport = $instance;
        }

        return $this->doxport;
    }

    /**
     * @param Doxport $doxport
     * @return Action
     */
    abstract protected function getAction(Doxport $doxport);

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        if ($this->doxport && $this->cleanup) {
            $factory = $this->doxport->getFileFactory();
            $path    = $factory->getPath();

            if ($factory->pathExists() && substr($path, 0, strlen(self::$root)) == self::$root) {
                $fs = new Filesystem();
                $fs->remove($path);
            }
        }

        $this->em      = null;
        $this->doxport = null;
    }
}
