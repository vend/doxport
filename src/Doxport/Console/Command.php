<?php

namespace Doxport\Console;

use Doctrine\ORM\EntityManager;
use Doxport\Doxport;
use Doxport\Log\OutputLogger;
use Doxport\Metadata\Driver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command as CommandComponent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends CommandComponent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Doxport
     */
    protected $doxport;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->logger) {
            $this->logger = new OutputLogger($output);
        }

        $this->configureDoxport($input);
    }

    protected function configureDoxport(InputInterface $input)
    {
        $this->doxport = new Doxport($this->getHelper('em')->getEntityManager());
        $this->doxport->setLogger($this->logger);

        return $this->doxport;
    }
}
