<?php

namespace Doxport\File;

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
        $array = [
            'first' => 'hello',
            'second' => 'world'
        ];

        $instance = $this->getInstance();
        $instance->writeObject($array);
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
