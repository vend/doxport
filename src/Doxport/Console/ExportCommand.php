<?php

namespace Doxport\Console;

use Doxport\Action\Export;
use Doxport\CriteriaBuilder;
use Doxport\JoinPass;
use Doxport\Schema;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('export')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin exporting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit exporting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->setDescription('Exports a set of data from the database, beginning with a specified type');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

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

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->write((string)$schema);
        }

        $output->write('Doing export...');

        $export = new Export($this->getEntityManager(), $schema);
        $export->run();

        $output->writeln('done.');
    }
}
