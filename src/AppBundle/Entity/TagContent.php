<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TagContent
 *
 * @ORM\Table(name="tag_content")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagContentRepository")
 */
class TagContent
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
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var bool
     *
     * @ORM\Column(name="suggested", type="boolean")
     */
    private $suggested;

    /**
     * @var TagName
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TagName", inversedBy="contents", fetch="EAGER")
     */
    private $tag;

    /**
     * @var Adventure
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adventure", inversedBy="info")
     */
    private $adventure;

    public function __construct()
    {
        $this->suggested = true;
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
     * Set content
     *
     * @param string $content
     *
     * @return TagContent
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set suggested
     *
     * @param boolean $suggested
     *
     * @return TagContent
     */
    public function setSuggested($suggested)
    {
        $this->suggested = $suggested;

        return $this;
    }

    /**
     * Get suggested
     *
     * @return bool
     */
    public function getSuggested()
    {
        return $this->suggested;
    }

    /**
     * @return TagName
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param TagName $tag
     *
     * @return TagContent
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Adventure
     */
    public function getAdventure()
    {
        return $this->adventure;
    }

    /**
     * @param Adventure $adventure
     *
     * @return TagContent
     */
    public function setAdventure($adventure)
    {
        $this->adventure = $adventure;
        $this->adventure->addInfo($this);

        return $this;
    }
}

