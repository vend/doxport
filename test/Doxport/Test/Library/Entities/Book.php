<?php

namespace Doxport\Test\Fixtures\Library\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doxport\Annotation as Export;

/**
 * @ORM\Entity
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $title;

    /**
     * @var Author
     *
     * @ORM\ManyToOne(targetEntity="Author")
     */
    protected $author;

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param Author $author
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }
}
