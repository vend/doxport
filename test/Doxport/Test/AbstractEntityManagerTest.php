<?php

namespace Doxport\Test;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LogicException;

abstract class AbstractEntityManagerTest extends AbstractTest
{
    /**
     * @var string
     */
    protected $fixtures = 'Bookstore';

    /**
     * @var array<string>
     */
    protected $fixtureTypes = [
        'User'      => 'yaml',
        'Bookstore' => 'annotation'
    ];

    /**
     * @var ArrayCache
     */
    protected $cache;

    /**
     * @var Configuration
     */
    protected $config;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->setUpCache();
        $this->setUpConfig();
    }

    /**
     * @return array<string>
     */
    protected function getConnectionOptions()
    {
        return [
            'driver' => 'pdo_sqlite',
            'path'   => 'database.sqlite'
        ];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return EntityManager::create($this->getConnectionOptions(), $this->config);
    }

    /**
     * @return void
     */
    protected function setUpCache()
    {
        $this->cache = new ArrayCache();
    }

    /**
     * @return void
     */
    protected function setUpConfig()
    {
        $this->config = new Configuration();

        $this->config->setMetadataCacheImpl($this->cache);
        $this->config->setQueryCacheImpl($this->cache);

        $this->config->setMetadataDriverImpl($this->getMetadataImplementation());

        $this->config->setProxyDir('build/tmp/proxies');
        $this->config->setProxyNamespace('Doxport\Test\Proxies');
        
        $this->config->setAutoGenerateProxyClasses(true);
    }

    /**
     * @return MappingDriver
     */
    protected function getMetadataImplementation()
    {
        $type = $this->fixtureTypes[$this->fixtures];

        $fixtureDir = __DIR__ . DIRECTORY_SEPARATOR
            . 'Fixtures' . DIRECTORY_SEPARATOR
            . $this->fixtures;

        $entityDir = $fixtureDir . DIRECTORY_SEPARATOR
            . 'Entities';

        switch ($type) {
            case 'annotation':
                return $this->config->newDefaultAnnotationDriver($entityDir);
            case 'yaml':
                $driver = new YamlDriver([$fixtureDir]);
                return $driver;
            default:
                throw new LogicException(
                    'Cannot get metadata implementation for unknown fixture type'
                );
        }

    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->cache  = null;
        $this->config = null;
    }
}
