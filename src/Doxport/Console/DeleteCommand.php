<?php

namespace Doxport\Console;

use Doxport\Action\Delete;
use Doxport\Schema;
use Doxport\EntityGraph;
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

        $graph = new EntityGraph();
        $pass = new ConstraintPass($driver, $graph, $input->getArgument('entity'));
        $vertices = $pass->run();

        $graph->export($dir . '/constraints.png');

        $pass = new JoinPass($vertices, $driver, new Delete($this->getEntityManager()));
        $pass->run();

        $output->writeln('All done.');

        //$schema = $this->prepareSchema($input, $output);
        //$this->writeSchemaToOutput($schema, $output);
        //$this->performDelete($schema, $output);
    }

    /**
     * @param Schema          $schema
     * @param OutputInterface $output
     * @return void
     */
    protected function performDelete(Schema $schema, OutputInterface $output)
    {
        $output->write('Doing delete...');

        $delete = new ArchiveDelete($this->getEntityManager(), $schema, $output);
        $delete->setOutputInterface($output);
        $delete->run();

        $output->writeln('done.');
    }

    /**
     * @param Schema          $schema
     * @param OutputInterface $output
     * @return void
     */
    protected function writeSchemaToOutput(Schema $schema, OutputInterface $output)
    {
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->write((string)$schema);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return Schema
     */
    protected function prepareSchema(InputInterface $input, OutputInterface $output)
    {
        $output->write('Preparing schema for export...');

        $schema = new Schema($this->getMetadataDriver());

        $schema->getCriteria($input->getArgument('entity'))
            ->addWhereEq($input->getArgument('column'), $input->getArgument('value'));
        $schema->setRoot($input->getArgument('entity'));

        $output->writeln('done.');
        $output->write('Choosing path through tables...');

        $pass = new JoinPass($this->getMetadataDriver(), $schema);
        $pass->reduce();

        $output->writeln('done.');

        return $schema;
    }
}
