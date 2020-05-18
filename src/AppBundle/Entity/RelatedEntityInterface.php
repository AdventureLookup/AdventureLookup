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

    public function getAdventures(): Collection;

    /**
     * @return static
     */
    public function addAdventure(Adventure $adventure);

    /**
     * @return static
     */
    public function removeAdventure(Adventure $adventure);
}
