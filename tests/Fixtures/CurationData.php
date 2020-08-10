<?php

namespace Tests\Fixtures;

use AppBundle\Entity\Adventure;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CurationData extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $adventure = new Adventure();
        $adventure->setTitle('Adventure 1');
        $adventure->setLink('https://example.com/1.pdf');
        $adventure->setThumbnailUrl('https://example.com/1.png');
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('Adventure 2');
        $adventure->setLink('https://example.com/2.pdf');
        $adventure->setThumbnailUrl('https://example.com/2.png');
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('Adventure 3');
        $adventure->setLink('https://test.example.com/3.pdf');
        $adventure->setThumbnailUrl('https://test.example.com/3.png');
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('Adventure 4');
        $em->persist($adventure);

        $em->flush();
    }
}
