<?php

namespace Doxport\Test\Fixtures\Bookstore\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doxport\Annotation as Export;

/**
 * @ORM\Entity
 */
class Author
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
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $lastName;

    /**
     * Joins back down to the book table, causing a cycle in the dependency graph
     *
     * We solve the cycle by excluding this relation from consideration, and
     * preprocessing this column when exporting/deleting (and postprocessing
     * for import)
     *
     * @Export\Exclude
     * @Export\Clear
     * @ORM\OneToOne(targetEntity="Book")
     */
    protected $favoriteWork;

    /**
     * @param string $first
     * @param string $last
     */
    public function setName($first, $last)
    {
        $this->firstName = $first;
        $this->lastName = $last;
    }

    /**
     * @param Book $book
     */
    public function setFavouriteWork(Book $book)
    {
        $this->favoriteWork = $book;
    }
}
