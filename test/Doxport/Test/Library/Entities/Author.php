<?php

namespace Doxport\Test\Fixtures\Bookstore\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doxport\Annotation as Export;

/**
 * @Table
 * @Entity
 */
class Author
{
    /**
     * @Id
     * @GeneratedValue(strategy="NONE")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string", length=200)
     */
    protected $firstName;

    /**
     * @Column(type="string", length=200)
     */
    protected $lastName;
}
