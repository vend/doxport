<?php

namespace Doxport;

use Doxport\Action\Base\Action;
use Doxport\Action\Import;
use Doxport\Exception\IOException;
use Doxport\Test\AbstractEntityManagerTest;

class ImportTest extends AbstractEntityManagerTest
{
    /**
     * @expectedException Doxport\Exception\IOException
     */
    public function testEmptyDirectory()
    {
        $this->doxport->getAction()->run();
    }

    /**
     * Test the import process
     */
    public function testImport()
    {
        $doxport = $this->getDoxport();
        $doxport->getFileFactory()->setPath(self::getExportDirectory());

        $doxport->getAction()->run();
    }

    /**
     * @return string
     */
    protected static function getExportDirectory()
    {
        return self::getFixtureDirectory() . DIRECTORY_SEPARATOR . 'Exported';
    }

    /**
     * @param Doxport $doxport
     * @return Action
     */
    protected function getAction(Doxport $doxport)
    {
        $action = new Import($this->em);
        return $action;
    }
}
