<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\Export;
use Doxport\Console\Base\QueryActionCommand;

/**
 * Exports a dataset, writing it to files
 */
class ExportCommand extends QueryActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('export')
            ->setDescription('Exports a set of data from the database, beginning with a specified type');
    }

    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Export($this->doxport->getEntityManager());
    }
}
