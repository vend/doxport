<?php

namespace Doxport\File;

use stdClass;

class JsonFileTest extends AbstractFileTest
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
            '{"first":"hello","second":"world"}' . "\n" . '{"third":"and","fourth":"again"}' . "\n",
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
        $obj->third  = '00000';
        $obj->fourth = '0';
        $obj->fifth  = 0;
        $obj->sixth  = false;

        $instance = $this->getInstance();
        $instance->writeObject($obj);
        $instance->flush();
        $instance->close();

        $other = $this->getInstance();
        $string = $other->readAll();
        $other->close();

        $this->assertEquals(
            '{"first":"hello","second":"world","third":"00000","fourth":"0","fifth":0,"sixth":false}' . "\n",
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
        $other->rewind();
        $object = $other->readObject();
        $other->close();

        $this->assertArrayHasKey('binary', $object);
        $this->assertEquals($str, $object['binary']);
    }

    public function testReadObject()
    {
        $path = self::getExportedDirectory() . DIRECTORY_SEPARATOR . 'Book.json';

        $file = $this->getInstance($path);
        $file->rewind();

        $book = $file->readObject();
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('author_id', $book);
        $this->assertArrayHasKey('title', $book);

        $book = $file->readObject();
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('author_id', $book);
        $this->assertArrayHasKey('title', $book);

        $book = $file->readObject();
        $this->assertFalse($book);
    }

    /**
     * @return string
     */
    protected function getClassUnderTest()
    {
        return 'Doxport\File\JsonFile';
    }
}
