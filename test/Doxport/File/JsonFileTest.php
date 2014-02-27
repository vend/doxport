<?php

namespace Doxport\File;

use stdClass;

class JsonFileTest extends AsyncFileTest
{
    /**
     * @return string
     */
    protected function getClassUnderTest()
    {
        return 'Doxport\File\JsonFile';
    }



    /**
     * @return void
     */
    public function testWriteObject()
    {
        $obj = new stdClass;
        $obj->first  = 'hello';
        $obj->second = 'world';

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->close();

        $obj = new stdClass;
        $obj->third  = 'and';
        $obj->fourth = 'again';

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->close();

        $other = $this->getInstance();
        $string = $other->readAll();
        $other->close();

        $this->assertEquals(
            '[{"first":"hello","second":"world"},{"third":"and","fourth":"again"}]',
            $string,
            'Multiple writes then read JSON file'
        );
    }

    /**
     * @return void
     */
    public function testWriteObjectOnce()
    {
        $obj = new stdClass;
        $obj->first  = 'hello';
        $obj->second = 'world';

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->close();

        $other = $this->getInstance();
        $string = $other->readAll();
        $other->close();

        $this->assertEquals(
            '[{"first":"hello","second":"world"}]',
            $string,
            'Single write then read JSON file'
        );
    }
}
