<?php


namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface HasAdventuresInterface
{
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
