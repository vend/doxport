<?php

namespace Doxport\Test\Fixtures\Bookstore\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doxport\Annotation as Export;

/**
 * @Table()
 * @Entity()
 */
class Book
{
    /**
     * @Id
     * @GeneratedValue(strategy="NONE")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(name="name", type="string", length=200)
     */
    protected $name;

    /**
     * @var Author
     *
     * @ManyToOne(targetEntity="Author")
     */
    protected $author;
}
