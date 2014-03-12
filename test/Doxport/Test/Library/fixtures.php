<?php

namespace Doxport\Test\Fixtures\Bookstore;

use Doctrine\ORM\EntityManager;

/** @var EntityManager $em */

$kurt = new Entities\Author();
$kurt->setName('Kurt', 'Vonnegut');

$breakfast = new Entities\Book();
$breakfast->setTitle('Breakfast of Champions');
$breakfast->setAuthor($kurt);

$cat = new Entities\Book();
$cat->setTitle('Cat\'s Cradle');
$cat->setAuthor($kurt);

$zhuang = new Entities\Author();
$zhuang->setName('Zhuang', 'Zhou');

$zhuangzi = new Entities\Book();
$zhuangzi->setTitle('南華真經');
$zhuangzi->setAuthor($zhuang);

$em->persist($kurt);
$em->persist($breakfast);
$em->persist($cat);
$em->persist($zhuang);
$em->persist($zhuangzi);

$em->flush();
