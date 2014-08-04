<?php

namespace Doxport;

use Doxport\Action\HardDelete;
use Doxport\Test\AbstractQueryActionTest;

class HardDeleteTest extends AbstractQueryActionTest
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
        $action = new HardDelete($this->em);
        $action->addRootCriteria('firstName', 'Zhuang');
        return $action;
    }
}
