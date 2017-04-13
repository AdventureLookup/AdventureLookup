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
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var bool
     *
     * @ORM\Column(name="suggested", type="boolean")
     */
    private $suggested;


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
}

