<?php

namespace Tests\Fixtures;

use AppBundle\Entity\Adventure;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class SimilarTitlesData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $em)
    {
        $adventure = new Adventure();
        $adventure->setTitle('nature');
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('nature animal');
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('naturre'); // typo
        $em->persist($adventure);

        $adventure = new Adventure();
        $adventure->setTitle('animal');
        $em->persist($adventure);

        $em->flush();
    }
}
