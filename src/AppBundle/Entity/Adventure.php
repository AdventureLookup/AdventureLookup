<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var Author[]|Collection
     * @ORM\ManyToMany(targetEntity="Author", cascade={"persist"}, indexBy="adventures", inversedBy="adventures")
     */
    private $authors;

    /**
     * @var Edition
     * @ORM\ManyToOne(targetEntity="Edition", fetch="EAGER", inversedBy="adventures")
     */
    private $edition;

    /**
     * @var Environment[]|Collection
     * @ORM\ManyToMany(targetEntity="Environment", indexBy="adventures", inversedBy="adventures")
     */
    private $environments;

    /**
     * @var Item[]|Collection
     * @ORM\ManyToMany(targetEntity="Item", cascade={"persist"}, indexBy="adventures", inversedBy="adventures")
     */
    private $items;

    /**
     * @var Publisher
     * @ORM\ManyToOne(targetEntity="Publisher", fetch="EAGER", inversedBy="adventures")
     */
    private $publisher;

    /**
     * @var Setting
     * @ORM\ManyToOne(targetEntity="Setting", fetch="EAGER", inversedBy="adventures")
     */
    private $setting;

    /**
     * @var Monster[]|Collection
     * @ORM\ManyToMany(targetEntity="Monster", cascade={"persist"}, indexBy="adventures", inversedBy="adventures")
     * @ORM\OrderBy({"isUnique" = "DESC", "name" = "ASC"})
     */
    private $monsters;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $minStartingLevel;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $maxStartingLevel;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $startingLevelRange;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=1)
     */
    private $numPages;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $foundIn;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $partOf;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     * @Assert\Url()
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     * @Assert\Url()
     */
    private $thumbnailUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $soloable;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pregeneratedCharacters;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tacticalMaps;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
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
     * @var ChangeRequest[]|Collection
     *
     * @ORM\OneToMany(targetEntity="ChangeRequest", mappedBy="adventure", orphanRemoval=true)
     */
    private $changeRequests;

    /**
     * @var Review[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="adventure", orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $reviews;

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

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->info = new ArrayCollection();

        $this->authors = new ArrayCollection();
        $this->environments = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->monsters = new ArrayCollection();
        $this->changeRequests = new ArrayCollection();
        $this->reviews = new ArrayCollection();

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
     * @return Author[]|Collection
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    /**
     * @param Author $author
     *
     * @return Adventure
     */
    public function addAuthor(Author $author)
    {
        return $this->addRelatedEntity('authors', $author);
    }

    /**
     * @param Author[] $authors
     *
     * @return Adventure
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;
        return $this;
    }

    /**
     * @return Edition
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * @param Edition $edition
     *
     * @return Adventure
     */
    public function setEdition(Edition $edition = null)
    {
        return $this->setRelatedEntity('edition', $edition);
    }

    /**
     * @return Environment[]|Collection
     */
    public function getEnvironments(): Collection
    {
        return $this->environments;
    }

    /**
     * @param Environment $environment
     *
     * @return Adventure
     */
    public function addEnvironment(Environment $environment)
    {
        return $this->addRelatedEntity('environments', $environment);
    }

    /**
     * @param Environment[] $environments
     *
     * @return Adventure
     */
    public function setEnvironments($environments)
    {
        $this->environments = $environments;
        return $this;
    }

    /**
     * @return Item[]|Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Item $item
     *
     * @return Adventure
     */
    public function addItem(Item $item)
    {
        return $this->addRelatedEntity('items', $item);
    }

    /**
     * @param Item[] $items
     *
     * @return Adventure
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param Publisher $publisher
     *
     * @return Adventure
     */
    public function setPublisher(Publisher $publisher = null)
    {
        return $this->setRelatedEntity('publisher', $publisher);
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
    public function setSetting(Setting $setting = null)
    {
        return $this->setRelatedEntity('setting', $setting);
    }

    /**
     * @return Monster[]|Collection
     */
    public function getMonsters()
    {
        return $this->monsters;
    }

    /**
     * @return Monster[]|Collection
     */
    public function getCommonMonsters()
    {
        return $this->monsters->filter(function (Monster $monster) {
            return !$monster->getIsUnique();
        });
    }

    /**
     * @return Monster[]|Collection
     */
    public function getBossMonsters()
    {
        return $this->monsters->filter(function (Monster $monster) {
            return $monster->getIsUnique();
        });
    }

    /**
     * @param Monster $monster
     * @return Adventure
     */
    public function addMonster(Monster $monster)
    {
        return $this->addRelatedEntity('monsters', $monster);
    }

    /**
     * @param Monster[]|Collection $monsters
     * @return Adventure
     */
    public function setMonsters($monsters)
    {
        $this->monsters = $monsters;
        return $this;
    }

    /**
     * @param Monster[]|Collection $monsters
     * @return Adventure
     */
    public function setCommonMonsters($monsters)
    {
        $this->monsters = $this->monsters->filter(function (Monster $monster) {
            return $monster->getIsUnique();
        });
        foreach ($monsters as $monster) {
            $this->monsters->add($monster);
        }
        return $this;
    }

    /**
     * @param Monster[]|Collection $monsters
     * @return Adventure
     */
    public function setBossMonsters($monsters)
    {
        $this->monsters = $this->monsters->filter(function (Monster $monster) {
            return !$monster->getIsUnique();
        });
        foreach ($monsters as $monster) {
            $this->monsters->add($monster);
        }
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
     * @return TagContent[]|Collection
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
    public function getDescription()
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
    public function getFoundIn()
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

    /**
     * @return string
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * @param string $partOf
     * @return Adventure
     */
    public function setPartOf($partOf)
    {
        $this->partOf = $partOf;

        return $this;
    }

    /**
     * @return ChangeRequest[]|Collection
     */
    public function getChangeRequests()
    {
        return $this->changeRequests;
    }

    /**
     * @return Review[]|Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @return int
     */
    public function getNumberOfThumbsUp()
    {
        return $this->reviews->filter(function (Review $review) {
            return $review->isThumbsUp();
        })->count();
    }

    /**
     * @return int
     */
    public function getNumberOfThumbsDown()
    {
        return $this->reviews->filter(function (Review $review) {
            return $review->isThumbsDown();
        })->count();
    }

    /**
     * @param User|null $user
     *
     * @return Review|null
     */
    public function getReviewBy(User $user = null)
    {
        if ($user === null) {
            return null;
        }

        $reviews = $this->reviews->filter(function (Review $review) use ($user) {
            return $review->getCreatedBy() == $user->getUsername();
        });

        return $reviews->count() > 0 ? $reviews->first() : null;
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

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param string $field
     * @param RelatedEntityInterface|null $relatedEntity
     * @return $this
     */
    private function setRelatedEntity(string $field, RelatedEntityInterface $relatedEntity = null): Adventure
    {
        if ($this->$field !== null) {
            $this->$field->removeAdventure($this);
        }
        if ($relatedEntity !== null) {
            $relatedEntity->addAdventure($this);
        }
        $this->$field = $relatedEntity;

        return $this;
    }

    /**
     * @param string $field
     * @param RelatedEntityInterface|null $relatedEntity
     * @return Adventure
     */
    private function addRelatedEntity(string $field, RelatedEntityInterface $relatedEntity = null): Adventure
    {
        $relatedEntity->addAdventure($this);
        $this->$field->add($relatedEntity);

        return $this;
    }
}
