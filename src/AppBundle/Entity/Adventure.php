<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Adventure
 *
 * @ORM\Table(name="adventure")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdventureRepository")
 * @UniqueEntity("title")
 * @Gedmo\Loggable
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
     * @var Setting
     * @ORM\ManyToOne(targetEntity="Setting")
     * @Gedmo\Versioned()
     */
    private $setting;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Gedmo\Versioned()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Gedmo\Versioned()
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=1)
     * @Gedmo\Versioned()
     */
    private $minStartingLevel;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=1)
     * @Gedmo\Versioned()
     */
    private $maxStartingLevel;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned()
     */
    private $startingLevelRange;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=1)
     * @Gedmo\Versioned()
     */
    private $numPages;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned()
     */
    private $foundIn;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     * @Gedmo\Versioned()
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     * @Gedmo\Versioned()
     */
    private $thumbnailUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    private $soloable;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    private $pregeneratedCharacters;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    private $tacticalMaps;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    private $handouts;

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

    /**
     * @var string
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string", nullable=true)
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="change", field={"title"})
     */
    private $updatedBy;

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
     * @return Setting
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * @param Setting $setting
     * @return Adventure
     */
    public function setSetting(Setting $setting)
    {
        $this->setting = $setting;
        return $this;
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

    /**
     * @return int
     */
    public function getMinStartingLevel()
    {
        return $this->minStartingLevel;
    }

    /**
     * @param int $minStartingLevel
     *
     * @return Adventure
     */
    public function setMinStartingLevel($minStartingLevel)
    {
        $this->minStartingLevel = $minStartingLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxStartingLevel()
    {
        return $this->maxStartingLevel;
    }

    /**
     * @param int $maxStartingLevel
     *
     * @return Adventure
     */
    public function setMaxStartingLevel($maxStartingLevel)
    {
        $this->maxStartingLevel = $maxStartingLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartingLevelRange()
    {
        return $this->startingLevelRange;
    }

    /**
     * @param string $startingLevelRange
     *
     * @return Adventure
     */
    public function setStartingLevelRange($startingLevelRange)
    {
        $this->startingLevelRange = $startingLevelRange;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * @param int $numPages
     *
     * @return Adventure
     */
    public function setNumPages($numPages)
    {
        $this->numPages = $numPages;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Adventure
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }

    /**
     * @param string $thumbnailUrl
     *
     * @return Adventure
     */
    public function setThumbnailUrl($thumbnailUrl)
    {
        $this->thumbnailUrl = $thumbnailUrl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSoloable()
    {
        return $this->soloable;
    }

    /**
     * @param bool $soloable
     *
     * @return Adventure
     */
    public function setSoloable($soloable)
    {
        $this->soloable = $soloable;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPregeneratedCharacters()
    {
        return $this->pregeneratedCharacters;
    }

    /**
     * @param bool $pregeneratedCharacters
     *
     * @return Adventure
     */
    public function setPregeneratedCharacters($pregeneratedCharacters)
    {
        $this->pregeneratedCharacters = $pregeneratedCharacters;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTacticalMaps()
    {
        return $this->tacticalMaps;
    }

    /**
     * @param bool $tacticalMaps
     *
     * @return Adventure
     */
    public function setTacticalMaps($tacticalMaps)
    {
        $this->tacticalMaps = $tacticalMaps;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasHandouts()
    {
        return $this->handouts;
    }

    /**
     * @param bool $handouts
     *
     * @return Adventure
     */
    public function setHandouts($handouts)
    {
        $this->handouts = $handouts;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Adventure
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getFoundIn(): string
    {
        return $this->foundIn;
    }

    /**
     * @param string $foundIn
     *
     * @return Adventure
     */
    public function setFoundIn($foundIn)
    {
        $this->foundIn = $foundIn;

        return $this;
    }
}

