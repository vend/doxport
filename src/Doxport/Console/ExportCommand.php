<?php

namespace Doxport\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('export')
            ->addOption(
                'bootstrap'
            )
            ->setDescription('Exports a set of data from the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
