<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Monster
 *
 * @ORM\Table(name="monster")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MonsterRepository")
 */
class Monster
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
     * @ORM\ManyToMany(targetEntity="MonsterType", inversedBy="monsters")
     * @ORM\JoinTable(name="monster_monstertype")
     */
    private $types;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_unique", type="boolean")
     */
    private $isUnique = false;

    public function __construct()
    {
        $this->types = new ArrayCollection();
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
     * Add a monster type
     *
     * @param MonsterType $type
     * @return $this
     */
    public function addType(MonsterType $type)
    {
        $type->addMonster($this);
        $this->types[] = $type;

        return $this;
    }

    /**
     * Get monster types
     *
     * @return MonsterType[]|ArrayCollection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Monster
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
     * Set isUnique
     *
     * @param boolean $isUnique
     *
     * @return Monster
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * Get isUnique
     *
     * @return bool
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }
}

