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

    public function testWriteObjectBinary()
    {
        $str = '';

        for ($i = 0; $i <= 255; $i++) {
            $str .= chr($i);
        }

        $obj = [
            'binary' => $str
        ];

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->close();

        $other = $this->getInstance();
        $objects = $other->readObjects();
        $other->close();

        $this->assertCount(1, $objects, 'One object returned');

        $result = $objects[0];

        $this->assertArrayHasKey('binary', $result);
        $this->assertEquals($str, $result['binary']);
    }
}
