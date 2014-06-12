<?php

namespace Doxport\Util;

use Doxport\Test\AbstractTest;

class ChunkTest extends AbstractTest
{
    public function testInitialEstimate()
    {
        $chunk = new Chunk(500);
        $this->assertInstanceOf('Doxport\Util\Chunk', $chunk);
        $this->assertEquals(500, $chunk->getEstimatedSize());
    }

    public function testTakingLonger()
    {
        // We target 1000 rows, and 10 seconds
        $chunk = new Chunk(1000, 10);

        // But we took 20 seconds instead!
        $chunk->interval(20);

        // So, now we should try to process less rows
        $this->assertLessThan(1000, $chunk->getEstimatedSize());
    }

    public function testTakingShorter()
    {
        // We target 1000 rows, and 10 seconds
        $chunk = new Chunk(1000, 10);

        // But we took 5 seconds instead!
        $chunk->interval(5);

        // So, now we should try to process more rows
        $this->assertGreaterThan(1000, $chunk->getEstimatedSize());
    }

    public function testMinMax()
    {
        $chunk = new Chunk(1000, 10, [
            'min'       => 100,
            'max'       => 1000,
            'smoothing' => 0.99
        ]);

        $chunk->interval(0.1);
        $chunk->interval(0.1);
        $chunk->interval(0.1);

        $this->assertEquals(1000, $chunk->getEstimatedSize());

        $chunk->interval(1000);
        $chunk->interval(1000);
        $chunk->interval(1000);

        $this->assertEquals(100, $chunk->getEstimatedSize());
    }
}