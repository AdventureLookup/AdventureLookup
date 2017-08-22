<?php


namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface RelatedEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return Collection
     */
    public function getAdventures(): Collection;

    /**
     * @param Adventure $adventure
     * @return static
     */
    public function addAdventure(Adventure $adventure);
}
