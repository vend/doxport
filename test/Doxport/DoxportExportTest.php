<?php

namespace Doxport;

use Doxport\Action\Base\Action;
use Doxport\Action\Export;
use Doxport\Test\AbstractActionTest;

class DoxportExportTest extends AbstractActionTest
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
        $action = new Export($this->em);
        $action->addRootCriteria('firstName', 'Kurt');
        return $action;
    }
}
