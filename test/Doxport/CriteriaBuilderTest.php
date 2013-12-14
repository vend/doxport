<?php

use Doctrine\ORM\EntityManager;

class CriteriaBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $builder = new \Doxport\CriteriaBuilder($this->getMockEntityManager());
        $this->assertInstanceOf('Doxport\CriteriaBuilder', $builder);
    }

    /**
     * @return EntityManager
     */
    protected function getMockEntityManager()
    {
        return $this->getMockBuilder('\Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();
    }
}
