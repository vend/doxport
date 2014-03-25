<?php

namespace Doxport\Test\Fixtures\Library;

use Doctrine\ORM\EntityManager;

/** @var EntityManager $em */

// ---

$cat = new Entities\Book();
$cat->setTitle('Cat\'s Cradle');

$kurt = new Entities\Author();
$kurt->setName('Kurt', 'Vonnegut');
$kurt->setFavouriteWork($cat);

$cat->setAuthor($kurt);

$breakfast = new Entities\Book();
$breakfast->setTitle('Breakfast of Champions');
$breakfast->setAuthor($kurt);

// ---

$zhuang = new Entities\Author();
$zhuang->setName('Zhuang', 'Zhou');

$zhuangzi = new Entities\Book();
$zhuangzi->setTitle('南華真經');
$zhuangzi->setAuthor($zhuang);

// ---

$em->persist($kurt);
$em->persist($breakfast);
$em->persist($cat);
$em->persist($zhuang);
$em->persist($zhuangzi);

$em->flush();
