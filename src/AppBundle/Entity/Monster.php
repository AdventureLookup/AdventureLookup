<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Monster
 *
 * @ORM\Table(name="monster", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="name_and_isUnique", columns={"name", "is_unique"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MonsterRepository")
 * @UniqueEntity({"name", "isUnique"})
 */
class Monster implements RelatedEntityInterface
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_unique", type="boolean")
     */
    private $isUnique = false;

    /**
     * @var Adventure[]|Collection
     * @ORM\ManyToMany(targetEntity="Adventure", mappedBy="monsters")
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
        $this->isUnique = false;
        $this->types = new ArrayCollection();
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
     * Add a monster type
     *
     * @return $this
     */
    public function addType(MonsterType $type)
    {
        $type->addMonster($this);
        $this->types[] = $type;

        return $this;
    }

    /**
     * Set the monster types
     *
     * @return $this
     */
    public function setTypes(Collection $types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }

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
     * @param bool $isUnique
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
