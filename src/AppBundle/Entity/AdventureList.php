<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdventureList
 *
 * @ORM\Table(name="adventure_list")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdventureListRepository")
 */
class AdventureList
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @Gedmo\Blameable(on="create")
     */
    private $user;

    /**
     * @var Adventure[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Adventure", fetch="EXTRA_LAZY")
     */
    private $adventures;

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
     * @return AdventureList
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Adventure[]|Collection
     */
    public function getAdventures(): Collection
    {
        return $this->adventures;
    }

    public function addAdventure(Adventure $adventure)
    {
        $this->adventures->add($adventure);
    }

    public function removeAdventure(Adventure $adventure)
    {
        foreach ($this->adventures as $key => $listedAdventure) {
            if ($listedAdventure->getId() === $adventure->getId()) {
                $this->adventures->remove($key);

                return;
            }
        }
    }

    /**
     * @param Adventure $adventure
     * @return bool
     */
    public function containsAdventure(Adventure $adventure): bool
    {
        foreach ($this->adventures as $listedAdventure) {
            if ($listedAdventure->getId() === $adventure->getId()) {
                return true;
            }
        }

        return false;
    }
}

