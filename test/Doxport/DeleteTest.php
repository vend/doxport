<?php

namespace Doxport;

use Doxport\Action\Delete;
use Doxport\Test\AbstractQueryActionTest;

class DeleteTest extends AbstractQueryActionTest
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

        $instance->setEntity('Doxport\Test\Fixtures\Library\Entities\Author');
        $instance->setOption('root', true);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    protected function getAction(Doxport $doxport)
    {
        $action = new Delete($this->em);
        $action->addRootCriteria('firstName', 'Zhuang');
        return $action;
    }
}
