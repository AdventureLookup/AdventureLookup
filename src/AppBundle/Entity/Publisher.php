<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Publisher
 *
 * @ORM\Table(name="publisher")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PublisherRepository")
 * @UniqueEntity("name")
 */
class Publisher implements RelatedEntityInterface
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var Adventure[]|Collection
     * @ORM\OneToMany(targetEntity="Adventure", mappedBy="publisher")
     */
    private $adventures;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    private $updatedBy;

    public function __construct()
    {
        $this->adventures = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Publisher
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Adventure[]|Collection
     */
    public function getAdventures(): Collection
    {
        return $this->adventures;
    }

    /**
     * @return static
     */
    public function addAdventure(Adventure $adventure)
    {
        $this->adventures->add($adventure);

        return $this;
    }

    /**
     * @return static
     */
    public function removeAdventure(Adventure $adventure)
    {
        $this->adventures->removeElement($adventure);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
