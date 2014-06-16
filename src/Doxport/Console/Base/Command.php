<?php

namespace Doxport\Console\Base;

use Doxport\Doxport;
use Doxport\Log\OutputLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command as CommandComponent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends CommandComponent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Doxport
     */
    protected $doxport;

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Verbose logging', null);
    }

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

    /**
     * @param InputInterface $input
     * @return Doxport
     */
    protected function configureDoxport(InputInterface $input)
    {
        $this->doxport = new Doxport($this->getHelper('em')->getEntityManager());
        $this->doxport->setLogger($this->logger);
        $this->doxport->setOption('verbose', $input->getOption('verbose'));

        return $this->doxport;
    }
}
