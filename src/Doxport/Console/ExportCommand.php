<?php

namespace Doxport\Console;

use Doxport\Action\Export;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends QueryActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('export')
            ->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'The data directory to archive to (default build/{action})', null)
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The format to export to (default json)', null)
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin exporting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit exporting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->setDescription('Exports a set of data from the database, beginning with a specified type, in the ' . \VendApplicationConfiguration::getActive()->getEnvironment() . ' env');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $pass = $this->getConstraintPass();
        $vertices = $pass->run();

        $pass = $this->getClearPass($vertices);
        $pass->run();

        $pass = $this->getJoinPass($vertices);
        $pass->run();

        $this->logger->notice('All done.');
    }


    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Export($this->getEntityManager());
    }
}
