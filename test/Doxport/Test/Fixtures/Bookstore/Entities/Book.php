<?php

namespace Doxport\Test\Fixtures\Bookstore\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doxport\Annotation as Export;

/**
 * @ORM\Entity
 */
class Book
{
    protected $id;

    protected $name;

    /**
     * @var Author
     *
     * @ORM\ManyToOne
     */
    protected $author;
}
