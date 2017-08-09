<?php


namespace Tests\Fixtures;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AdventureData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const NUM_ADVENTURES = 20;
    const ADVENTURES_PER_USER = 5;

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [UserData::class];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        $blameListener = $this->container->get('stof_doctrine_extensions.event_listener.blame');
        for ($i = 0; $i < self::NUM_ADVENTURES; $i++) {
            $adventureIndexByUser = $i % self::ADVENTURES_PER_USER;
            if ($adventureIndexByUser == 0) {
                $userReference = 'user-' . round($i / self::ADVENTURES_PER_USER);
                $blameListener->setUserValue($this->getReference($userReference)->getUsername());
            }
            $adventure = new Adventure();
            $adventure->setTitle("Adventure #{$i}");

            $em->persist($adventure);

            $this->addReference("adventure-{$i}", $adventure);
            $this->addReference("{$userReference}-adventure-{$adventureIndexByUser}", $adventure);
        }

        $em->flush();
    }
}
