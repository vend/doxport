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
     * @Export\Exclude
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
}
