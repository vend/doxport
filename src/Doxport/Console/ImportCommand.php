<?php

namespace Doxport\Console;

use Doxport\Action\Import;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('import')
            ->addArgument('data-dir', InputArgument::REQUIRED, 'The data directory to import', null)
            ->setDescription('Imports a set of exported data into the database');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->doxport->getAction()->run();

        $this->logger->notice('All done.');
    }

    /**
     * @return Import
     */
    protected function getAction()
    {
        return new Import($this->doxport->getEntityManager());
    }
}
