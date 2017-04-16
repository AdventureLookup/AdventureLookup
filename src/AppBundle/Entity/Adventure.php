<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Adventure
 *
 * @ORM\Table(name="adventure")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdventureRepository")
 */
class Adventure
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
     * @var int
     *
     * @ORM\Version
     * @ORM\Column(name="version", type="integer")
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(name="approved", type="boolean")
     */
    private $approved;

    /**
     * @var TagContent[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TagContent", fetch="EAGER", mappedBy="adventure", orphanRemoval=true)
     */
    private $info;

    public function __construct()
    {
        $this->info = new ArrayCollection();
        $this->approved = false;
    }

    public function __toString()
    {
        return $this->title;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Adventure
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set title
     *
     * @param string $title
     *
     * @return Adventure
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return TagContent[]
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param TagContent[] $info
     *
     * @return Adventure
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    public function addInfo(TagContent $info)
    {
        $this->info->add($info);
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     *
     * @return Adventure
     */
    public function setApproved(bool $approved): Adventure
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}

