<?php

namespace Doxport\File;

use stdClass;

class CsvFileTest extends AbstractFileTest
{
    /**
     * @return string
     */
    protected function getClassUnderTest()
    {
        return 'Doxport\File\CsvFile';
    }

    public function testWriteObject()
    {
        $obj = new stdClass;
        $obj->first = 'hello';
        $obj->second = 'world';

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->close();

        $other = $this->getInstance();
        $string = $other->readAll();
        $other->close();

        $this->assertEquals("hello,world\n", $string, 'Write then read CSV file');
    }

    /**
     * @return void
     */
    public function testReadObject()
    {
    }
}
