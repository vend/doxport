<?php

namespace Doxport;

use Doxport\Action\Export;
use Doxport\Test\AbstractQueryActionTest;

class ExportTest extends AbstractQueryActionTest
{
    /**
     * @var string
     */
    protected static $fixture = 'Library';

    /**
     * @return Doxport
     */
    protected function getDoxport()
    {
        $instance = parent::getDoxport();

        $instance->setEntity('Doxport\Test\Fixtures\Bookstore\Entities\Author');
        $instance->setOption('root', true);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    protected function getAction(Doxport $doxport)
    {
        $action = new Export($this->em);
        $action->addRootCriteria('firstName', 'Kurt');
        return $action;
    }
}
