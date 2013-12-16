<?php

namespace Doxport\Console;

use Doctrine\ORM\EntityManager;
use Doxport\Metadata\Driver;
use Symfony\Component\Console\Command\Command as CommandComponent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Nada.
    }
}
