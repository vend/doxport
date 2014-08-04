<?php

namespace Doxport\Console;

use Doxport\Action\Base\Action;
use Doxport\Action\HardDelete;
use Doxport\Console\Base\QueryActionCommand;

/**
 * Deletes from the database, without archiving or saving to file
 */
class HardDeleteCommand extends QueryActionCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('hard-delete')
            ->setDescription('(Hard) deletes a set of data from the database, but does not save or export the data');
    }

    /**
     * @return Action
     */
    protected function getAction()
    {
        return new HardDelete($this->doxport->getEntityManager());
    }
}
