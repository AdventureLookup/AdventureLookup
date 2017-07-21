<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MonsterType
 *
 * @ORM\Table(name="monster_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MonsterTypeRepository")
 */
class MonsterType
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
     * @ORM\ManyToMany(targetEntity="Monster", mappedBy="types")
     */
    private $monsters;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    public function __construct()
    {
        $this->types = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Add a monster to this type
     *
     * @param Monster $monster
     *
     * @return MonsterType
     */
    public function addMonster(Monster $monster)
    {
        $this->monsters[] = $monster;

        return $this;
    }

    /**
     * Get monsters
     *
     * @return ArrayCollection
     */
    public function getMonsters()
    {
        return $this->monsters;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MonsterType
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}

