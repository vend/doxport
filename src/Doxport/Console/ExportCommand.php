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
            ->setDescription('Exports a set of data from the database, beginning with a specified type');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $pass = $this->doxport->getConstraintPass();
        $vertices = $pass->run();

        $pass = $this->doxport->getClearPass($vertices);
        $pass->run();

        $pass = $this->doxport->getJoinPass($vertices);
        $pass->run();

        $this->logger->notice('All done.');
    }


    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Export($this->doxport->getEntityManager());
    }
}
