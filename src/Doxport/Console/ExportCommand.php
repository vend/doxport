<?php

namespace Doxport\Console;

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
        $this
            ->setName('export')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to begin exporting from', null)
            ->addArgument('column', InputArgument::REQUIRED, 'A column to limit exporting', null)
            ->addArgument('value', InputArgument::REQUIRED, 'The value to limit by', null)
            ->setDescription('Exports a set of data from the database, beginning with a specified type');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = new Schema($this->getMetadataDriver());
        $schema->getCriteria($input->getArgument('entity'))
            ->addWhereEq($input->getArgument('column'), $input->getArgument('value'));
        $schema->setRoot($input->getArgument('entity'));

        $pass = new JoinPass($this->getMetadataDriver(), $schema);
        $pass->reduce();

        echo (string)$schema;
    }
}
