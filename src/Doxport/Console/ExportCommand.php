<?php

namespace Doxport\Console;

use Doxport\CriteriaBuilder;
use Symfony\Component\Console\Command\Command;
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
        /* @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getHelper('em')->getEntityManager();

        $criteria = (new CriteriaBuilder($em))
            ->from($input->getArgument('entity'))
            ->where($input->getArgument('column'), $input->getArgument('value'))
            ->build();

        echo (string)$criteria;
    }
}
