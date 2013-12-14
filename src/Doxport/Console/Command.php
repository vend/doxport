<?php

namespace Doxport\Console;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Symfony\Component\Console\Command\Command as CommandComponent;

abstract class Command extends CommandComponent
{
    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getHelper('em')->getEntityManager();
    }

    /**
     * @return Driver
     */
    public function getMetadataDriver()
    {
        return new Driver($this->getEntityManager());
    }
}
