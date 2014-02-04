<?php

namespace Doxport\Util;

use Doxport\Test;

class AsyncFileTest extends Test
{
    protected $root = 'build/tmp';

    protected function setUp()
    {
        $this->file = $this->root . \DIRECTORY_SEPARATOR . __CLASS__;
        @unlink($this->file);
    }

    protected function tearDown()
    {
        @unlink($this->file);
    }

    public function testOpen()
    {
        $instance = new AsyncFile($this->file);
        $instance->open('a');
        $instance->write('Wow, ');
        $instance->writeln('a successful write to a new file');
        $instance->close();

        $other = new AsyncFile($this->file);
        $other->open('r');
        $string = $other->readAll();
        $other->close();

        $this->assertEquals("Wow, a successful write to a new file\n", $string, 'Write then read file');
    }

    public function testSync()
    {
        $instance = new AsyncFile($this->file);
        $instance->open('a');
        $instance->write('a');
        $instance->flush();
        $instance->write('bc');
        $instance->sync();
        $instance->close();

        $other = new AsyncFile($this->file);
        $other->open('r');
        $string = $other->readAll();
        $other->close();

        $this->assertEquals('abc', $string, 'Write then read file after sync');
    }

    public function testCsv()
    {
        $instance = new AsyncFile($this->file);
        $instance->open('a');
        $instance->writeCsvRow(['hello', 'world']);
        $instance->close();

        $other = new AsyncFile($this->file);
        $other->open('r');
        $string = $other->readAll();
        $other->close();

        $this->assertEquals("hello,world\n", $string, 'Write then read CSV file');
    }
}
