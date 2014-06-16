<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Delete;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes a dataset, writing it to files and syncing them before committing
 * the deletes to the database
 */
class DeleteCommand extends QueryActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('delete')
            ->setDescription('Deletes a set of data from the database, beginning with a specified type, but not including it');
    }

    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Delete($this->doxport->getEntityManager());
    }
}
