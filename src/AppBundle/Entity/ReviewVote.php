<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ReviewVote
 *
 * @ORM\Table(name="review_vote", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="review_and_user", columns={"review_id", "user_id"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReviewVoteRepository")
 *
 * @UniqueEntity(fields={"review", "user"})
 */
class ReviewVote
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Review
     *
     * @ORM\ManyToOne(targetEntity="Review", inversedBy="votes")
     *
     * @Assert\NotBlank()
     */
    private $review;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @Assert\NotBlank()
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="vote", type="boolean")
     *
     */
    private $vote;

    public function __construct(Review $review, User $user, bool $vote)
    {
        $this->vote = $vote;
        $this->review = $review;
        $this->user = $user;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vote
     *
     * @param integer $vote
     *
     * @return ReviewVote
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote
     *
     * @return int
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * @return bool
     */
    public function isUpvote(): bool
    {
        return $this->vote;
    }

    /**
     * @return bool
     */
    public function isDownvote(): bool
    {
        return !$this->vote;
    }
}

