<?php


namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Review;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAdventure()
    {
        $adventure = new Adventure();
        $review = new Review($adventure);
        
        $this->assertSame($adventure, $review->getAdventure());
    }

    public function testRating()
    {
        $review = new Review(new Adventure());

        $review->setRating(true);
        $this->assertTrue($review->isThumbsUp());
        $this->assertFalse($review->isThumbsDown());
        $this->assertTrue($review->getRating());

        $review->setRating(false);
        $this->assertFalse($review->isThumbsUp());
        $this->assertTrue($review->isThumbsDown());
        $this->assertFalse($review->getRating());

        $review->setThumbsUp();
        $this->assertTrue($review->isThumbsUp());
        $this->assertFalse($review->isThumbsDown());
        $this->assertTrue($review->getRating());

        $review->setThumbsDown();
        $this->assertFalse($review->isThumbsUp());
        $this->assertTrue($review->isThumbsDown());
        $this->assertFalse($review->getRating());
    }

    public function testComment()
    {
        $review = new Review(new Adventure());

        $this->assertNull($review->getComment());

        $review->setComment('bla bla');
        $this->assertSame('bla bla', $review->getComment());

        $review->setComment(null);
        $this->assertNull($review->getComment());

        $review->setComment('');
        $this->assertNull($review->getComment());
    }

    public function testGetId()
    {
        $review = new Review(new Adventure());

        $this->setPrivateProperty($review, 'id', 123);
        $this->assertSame(123, $review->getId());
    }

    public function testGetCreatedBy()
    {
        $review = new Review(new Adventure());

        $this->setPrivateProperty($review, 'createdBy', 'me!');
        $this->assertSame('me!', $review->getCreatedBy());
    }

    public function testGetCreatedAt()
    {
        $review = new Review(new Adventure());

        $createdAt = new \DateTime();

        $this->setPrivateProperty($review, 'createdAt', $createdAt);
        $this->assertSame($createdAt, $review->getCreatedAt());
    }

    private function setPrivateProperty(Review $review, string $property, $value)
    {
        $property = (new \ReflectionClass($review))->getProperty($property);
        $property->setAccessible(true);

        $property->setValue($review, $value);
    }
}
