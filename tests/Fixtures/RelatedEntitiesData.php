<?php

namespace Tests\Fixtures;

use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class RelatedEntitiesData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $em)
    {
        $entityClasses = [
            Author::class,
            Edition::class,
            Environment::class,
            Item::class,
            Monster::class,
            Publisher::class,
            Setting::class,
        ];

        foreach ($entityClasses as $entityClass) {
            $entityName = substr($entityClass, strrpos($entityClass, '\\') + 1);
            for ($i = 1; $i <= 5; ++$i) {
                $entity = new $entityClass();
                $entity->setName("{$entityName} {$i}");
                if ($entity instanceof Edition) {
                    $entity->setPosition($i * 10);
                }
                $em->persist($entity);
            }
        }
        $em->flush();
    }
}
