<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Table(name="review", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="adventure_and_createdBy", columns={"adventure_id", "created_by"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReviewRepository")
 *
 * @UniqueEntity(fields={"adventure", "createdBy"})
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Adventure
     *
     * @ORM\ManyToOne(targetEntity="Adventure", inversedBy="reviews")
     *
     * @Assert\NotBlank()
     */
    private $adventure;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(Adventure $adventure)
    {
        $this->adventure = $adventure;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Adventure
     */
    public function getAdventure()
    {
        return $this->adventure;
    }

    /**
     * @return Review
     */
    public function setThumbsUp()
    {
        $this->rating = true;

        return $this;
    }

    /**
     * @return Review
     */
    public function setThumbsDown()
    {
        $this->rating = false;

        return $this;
    }

    /**
     * @param bool $rating
     *
     * @return Review
     */
    public function setRating(bool $rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return bool
     */
    public function isThumbsUp()
    {
        return $this->rating == true;
    }

    /**
     * @return bool
     */
    public function isThumbsDown()
    {
        return $this->rating == false;
    }

    /**
     * @return bool
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param string $comment
     *
     * @return Review
     */
    public function setComment($comment)
    {
        if ($comment === '') {
            $comment = null;
        }
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

