<?php

namespace Doxport\File;

use stdClass;

abstract class AbstractJsonFileTest extends AbstractFileTest
{

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
        $instance->flush();
        $instance->close();

        $obj = new stdClass;
        $obj->third  = 'and';
        $obj->fourth = 'again';

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->flush();
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
        $instance->flush();
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
        $instance->flush();
        $instance->close();

        $other = $this->getInstance();
        $object = $other->readObject();
        $other->close();

        $this->assertArrayHasKey('binary', $object);
        $this->assertEquals($str, $object['binary']);
    }

    public function testReadObject()
    {
        $path = self::getExportedDirectory() . DIRECTORY_SEPARATOR . 'Book.json';

        $file = $this->getInstance($path);

        $object = $file->readObject();
        $this->assertArrayHasKey('title', $object);

        $object = $file->readObject();
        $this->assertArrayHasKey('title', $object);

        $object = $file->readObject();
        $this->assertFalse($object);
    }
}
