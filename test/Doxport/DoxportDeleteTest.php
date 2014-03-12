<?php

namespace Doxport;

use Doxport\Action\Base\Action;
use Doxport\Action\Delete;
use Doxport\Test\AbstractActionTest;

class DoxportDeleteTest extends AbstractActionTest
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
     * @return Action
     */
    protected function getAction()
    {
        $action = new Delete($this->em);
        $action->addRootCriteria('firstName', 'Zhuang');
        return $action;
    }
}
