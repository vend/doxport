<?php

namespace Doxport;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

abstract class EntityManagerTest extends AbstractTest
{
    protected $fixtures = 'Example';
    protected $cache;
    protected $config;

    protected function setUp()
    {
        $this->setUpCache();
        $this->setUpConfig();
    }

    protected function tearDown()
    {
        $this->cache  = null;
        $this->config = null;
    }

    protected function getConnectionOptions()
    {
        return [
            'driver' => 'pdo_sqlite',
            'path'   => 'database.sqlite'
        ];
    }

    protected function getEntityManager()
    {
        return EntityManager::create($this->getConnectionOptions(), $this->config);
    }

    protected function setUpCache()
    {
        $this->cache = new ArrayCache();
    }

    protected function setUpConfig()
    {
        $this->config = new Configuration();

        $this->config->setMetadataCacheImpl($this->cache);
        $this->config->setQueryCacheImpl($this->cache);

        $driverImpl = $this->config->newDefaultAnnotationDriver('/path/to/lib/MyProject/Entities');
        $this->config->setMetadataDriverImpl($driverImpl);

        $this->config->setProxyDir('/path/to/myproject/lib/MyProject/Proxies');
        $this->config->setProxyNamespace('MyProject\Proxies');
        
        $this->config->setAutoGenerateProxyClasses(true);
    }
}
