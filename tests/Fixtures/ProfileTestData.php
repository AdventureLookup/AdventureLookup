<?php


namespace Tests\Fixtures;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ProfileTestData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
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


        $blameListener->setUserValue($this->getReference('user-1')->getUsername());

        $myAdventure = new Adventure();
        $myAdventure->setTitle('My Adventure 1');

        $myUnresolvedChangeRequest = new ChangeRequest();
        $myUnresolvedChangeRequest->setAdventure($myAdventure);
        $myUnresolvedChangeRequest->setResolved(false);
        $myUnresolvedChangeRequest->setComment('My unresolved change request');

        $myResolvedChangeRequest = new ChangeRequest();
        $myResolvedChangeRequest->setAdventure($myAdventure);
        $myResolvedChangeRequest->setResolved(true);
        $myResolvedChangeRequest->setComment('My resolved change request');

        $myThumbsUpReview = new Review($myAdventure);
        $myThumbsUpReview->setThumbsUp();
        $myThumbsUpReview->setComment('My thumbs up review');

        $em->persist($myAdventure);
        $em->persist($myUnresolvedChangeRequest);
        $em->persist($myResolvedChangeRequest);
        $em->persist($myThumbsUpReview);
        $this->addReference('my-adventure-1', $myAdventure);
        $this->addReference('my-unresolved-change-request', $myUnresolvedChangeRequest);
        $this->addReference('my-resolved-change-request', $myResolvedChangeRequest);
        $this->addReference('my-thumbs-up-review', $myThumbsUpReview);

        $myAdventure2 = new Adventure();
        $myAdventure2->setTitle('My Adventure 2');

        $em->persist($myAdventure2);
        $this->addReference('my-adventure-2', $myAdventure2);

        $myAdventure3 = new Adventure();
        $myAdventure3->setTitle('My Adventure 3');

        $myResolvedChangeRequest2 = new ChangeRequest();
        $myResolvedChangeRequest2->setAdventure($myAdventure3);
        $myResolvedChangeRequest2->setResolved(true);
        $myResolvedChangeRequest2->setComment('My other resolved change request');

        $myThumbsUpReview2 = new Review($myAdventure3);
        $myThumbsUpReview2->setThumbsUp();
        $myThumbsUpReview2->setComment('My other thumbs up review');

        $em->persist($myAdventure3);
        $em->persist($myResolvedChangeRequest2);
        $em->persist($myThumbsUpReview2);
        $this->addReference('my-adventure-3', $myAdventure3);

        $blameListener->setUserValue($this->getReference('user-2')->getUsername());

        $yourAdventure = new Adventure();
        $yourAdventure->setTitle('Your Adventure');

        $yourUnresolvedChangeRequest = new ChangeRequest();
        $yourUnresolvedChangeRequest->setAdventure($myAdventure);
        $yourUnresolvedChangeRequest->setResolved(true);
        $yourUnresolvedChangeRequest->setComment('Your unresolved change request');

        $yourThumbsDownReview = new Review($myAdventure);
        $yourThumbsDownReview->setThumbsDown();
        $yourThumbsDownReview->setComment('Your thumbs down review');

        $em->persist($yourAdventure);
        $em->persist($yourUnresolvedChangeRequest);
        $em->persist($yourThumbsDownReview);
        $this->addReference('your-adventure', $yourAdventure);
        $this->addReference('your-unresolved-change-request', $yourUnresolvedChangeRequest);
        $this->addReference('your-thumbs-down-review', $yourThumbsDownReview);

        $em->flush();
    }
}
