<?php

namespace Doxport\Doctrine;

use Doxport\Test\AbstractTest;

class AliasGeneratorTest extends AbstractTest
{
    public function testGet()
    {
        $sut = new AliasGenerator();

        $alias = $sut->get('foo');
        $same = $sut->get('foo');
        $different = $sut->getAnother('foo');

        $this->assertEquals($alias, $same);
        $this->assertNotEquals($alias, $different);
    }

    public function testGetSameInitial()
    {
        $sut = new AliasGenerator();

        $foo = $sut->get('foo');
        $freak = $sut->get('freak');
        $fuck = $sut->get('fuck');

        $this->assertNotEquals($foo, $freak);
        $this->assertNotEquals($freak, $fuck);
        $this->assertNotEquals($fuck, $foo);
    }
}
