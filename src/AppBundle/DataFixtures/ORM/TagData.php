<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\TagName;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TagData implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->createTag('Publisher', 'text', $manager);
        $this->createTag('Environment', 'text', $manager);
        $this->createTag('Edition', 'text', $manager);
        $this->createTag('Setting', 'text', $manager);
        $this->createTag('Format', 'text', $manager);
        $this->createTag('Author', 'text', $manager);
        $this->createTag('Found in', 'text', $manager);
        $this->createTag('Items', 'text', $manager);
        $this->createTag('Villains', 'text', $manager);
        $this->createTag('Year', 'integer', $manager);
        $this->createTag('# of Pages', 'integer', $manager);
        $this->createTag('Min. Level', 'integer', $manager);
        $this->createTag('Max. Level', 'integer', $manager);
        $this->createTag('# of PCs', 'integer', $manager);
        $this->createTag('Tactical maps', 'boolean', $manager);
        $this->createTag('Handouts', 'boolean', $manager);

        $manager->flush();
    }

    private function createTag($title, $type, ObjectManager $manager)
    {
        $tag = new TagName();
        $tag->setTitle($title);
        $tag->setSuggested(false);
        $tag->setType($type);

        $manager->persist($tag);
    }
}