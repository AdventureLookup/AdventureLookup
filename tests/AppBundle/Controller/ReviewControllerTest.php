<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Review;
use Tests\Fixtures\AdventureData;
use Tests\WebTestCase;

class ReviewControllerTest extends WebTestCase
{
    const REVIEW_TEXT = "Hello World! This is my amazing review.";
    const REVIEW_TEXT_2 = "This is another review text.";

    /**
     * @dataProvider newReviewDataProvider
     */
    public function testCreateReview(bool $rating, string $comment = null)
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])
            ->getReferenceRepository();

        $em = $this->getContainer()->get('doctrine')->getManager();

        $adventure = $referenceRepository->getReference('user-1-adventure-1');

        $session = $this->makeSession(true);
        $session->visit("/adventures/{$adventure->getSlug()}");

        $session->getPage()->fillField('review_comment', $comment);
        if ($rating) {
            $session->getPage()->checkField('review_rating');
        } else {
            $session->getPage()->uncheckField('review_rating');
        }
        $session->getPage()->findById('review_form')->submit();

        $this->assertPath($session, "/adventures/{$adventure->getSlug()}");
        if ($comment !== null) {
            $this->assertTrue($session->getPage()->hasContent($comment));
        }

        $doctrine = $this->getContainer()->get('doctrine');
        $reviewRepository = $doctrine->getRepository(Review::class);
        /** @var Review[] $reviews */
        $reviews = $reviewRepository->findAll();
        $this->assertCount(1, $reviews);
        $em->refresh($reviews[0]);
        $this->assertSame($comment, $reviews[0]->getComment());
        $this->assertSame($rating, $reviews[0]->getRating());
        $this->assertSame('User #1', $reviews[0]->getCreatedBy());
    }

    public function newReviewDataProvider()
    {
        return [
            [
                true,
                null,
            ],
            [
                false,
                null,
            ],
            [
                true,
                self::REVIEW_TEXT,
            ],
            [
                false,
                self::REVIEW_TEXT,
            ],
        ];
    }

    public function testAddSecondReview()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])
            ->getReferenceRepository();
        $blameListener = $this->getContainer()
            ->get('stof_doctrine_extensions.event_listener.blame');
        $blameListener->setUserValue($referenceRepository
            ->getReference('user-1')->getUsername());

        $em = $this->getContainer()->get('doctrine')->getManager();
        $reviewRepository = $em->getRepository(Review::class);

        $adventure = $referenceRepository->getReference('user-1-adventure-1');

        $session = $this->makeSession(true);
        $session->visit("/adventures/{$adventure->getSlug()}");

        // Create a review (i.e. inside a second tab), while the user is
        // on the adventure page.
        $review = new Review($adventure);
        $review->setComment(self::REVIEW_TEXT);
        $review->setThumbsUp();
        $em->persist($review);
        $em->flush();
        $this->assertSame('User #1', $review->getCreatedBy());

        $session->getPage()->fillField('review_comment', self::REVIEW_TEXT_2);
        $session->getPage()->checkField('review_rating');
        $session->getPage()->findById('review_form')->submit();

        $this->assertPath($session, "/adventures/{$adventure->getSlug()}");
        $this->assertTrue($session->getPage()->hasContent('already created a review'));

        /** @var Review[] $reviews */
        $reviews = $reviewRepository->findAll();
        $this->assertCount(1, $reviews);
        $em->refresh($reviews[0]);
        $this->assertSame(self::REVIEW_TEXT, $reviews[0]->getComment());
    }

    public function testEdit()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])
            ->getReferenceRepository();
        $blameListener = $this->getContainer()
            ->get('stof_doctrine_extensions.event_listener.blame');
        $blameListener->setUserValue($referenceRepository
            ->getReference('user-1')->getUsername());

        $em = $this->getContainer()->get('doctrine')->getManager();
        $reviewRepository = $em->getRepository(Review::class);

        $adventure = $referenceRepository->getReference('user-1-adventure-1');
        $review = new Review($adventure);
        $review->setComment(self::REVIEW_TEXT);
        $review->setThumbsUp();
        $em->persist($review);
        $em->flush();

        $session = $this->makeSession(true);
        $session->visit("/adventures/{$adventure->getSlug()}");

        $this->assertTrue($session->getPage()->hasContent(self::REVIEW_TEXT));
        $this->assertTrue($session->getPage()->findField('review_rating')->isChecked());

        $session->getPage()->fillField('review_comment', self::REVIEW_TEXT_2);
        $session->getPage()->uncheckField('review_rating');
        $session->getPage()->findById('review_form')->submit();

        $this->assertPath($session, "/adventures/{$adventure->getSlug()}");
        $this->assertTrue($session->getPage()->hasContent(self::REVIEW_TEXT_2));

        /** @var Review[] $reviews */
        $reviews = $reviewRepository->findAll();
        $this->assertCount(1, $reviews);
        $em->refresh($reviews[0]);
        $this->assertSame(self::REVIEW_TEXT_2, $reviews[0]->getComment());
        $this->assertFalse($reviews[0]->getRating());
    }

    public function testDelete()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])
            ->getReferenceRepository();
        $blameListener = $this->getContainer()
            ->get('stof_doctrine_extensions.event_listener.blame');
        $blameListener->setUserValue($referenceRepository
            ->getReference('user-1')->getUsername());

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $adventure = $referenceRepository->getReference('user-1-adventure-1');
        $review = new Review($adventure);
        $review->setComment(self::REVIEW_TEXT);
        $review->setThumbsUp();
        $em->persist($review);
        $em->flush();

        $session = $this->makeSession(true);
        $session->visit("/adventures/{$adventure->getSlug()}");

        $this->assertTrue($session->getPage()->hasContent(self::REVIEW_TEXT));

        $session->getPage()->findButton('Delete Review')->click();

        $this->assertPath($session, "/adventures/{$adventure->getSlug()}");
        $this->assertFalse($session->getPage()->hasContent(self::REVIEW_TEXT));
    }
}
