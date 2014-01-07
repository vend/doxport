<?php

namespace Doxport\Console;

use Doxport\Action\Delete;
use Doxport\Schema;
use Doxport\EntityGraph;
use Doxport\Util\QueryAliases;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doxport\Pass\ConstraintPass;
use Doxport\Pass\JoinPass;

class DeleteCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('delete')
            ->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'The data directory to archive to', 'build/delete')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin deleting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit deleting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->setDescription('Deletes a set of data from the database, beginning with a specified type, but not including it');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $dir = $input->getOption('data-dir');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $driver = $this->getMetadataDriver();

        $graph = new EntityGraph($input->getArgument('entity'));
        $pass = new ConstraintPass($driver, $graph);
        $vertices = $pass->run();

        $graph->export($dir . '/constraints.png');

        $action = new Delete($this->getEntityManager(), new QueryAliases());
        $action->setLogger($this->logger);
        $action->addRootCriteria($input->getArgument('column'), $input->getArgument('value'));

        $graph = new EntityGraph($input->getArgument('entity'));
        $pass = new JoinPass($driver, $graph, $vertices, $action);
        $pass->run();

        $this->logger->notice('All done.');
    }
}
