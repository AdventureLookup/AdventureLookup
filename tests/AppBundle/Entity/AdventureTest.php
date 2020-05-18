<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AdventureTest extends TestCase
{
    /**
     * @var Adventure
     */
    private $subject;

    public function setUp()
    {
        $this->subject = new Adventure();

        $monsters = new \ReflectionProperty($this->subject, 'monsters');
        $monsters->setAccessible(true);
        $monsters->setValue($this->subject, new ArrayCollection([
            $this->makeCommonMonster(),
            $this->makeBossMonster(),
            $this->makeCommonMonster(),
            $this->makeBossMonster(),
        ]));

        $changeRequests = new \ReflectionProperty($this->subject, 'changeRequests');
        $changeRequests->setAccessible(true);
        $changeRequests->setValue($this->subject, new ArrayCollection([
            $this->makeChangeRequest(true),
            $this->makeChangeRequest(false),
        ]));
    }

    public function testGetChangeRequests()
    {
        $this->assertCount(2, $this->subject->getChangeRequests());
    }

    public function testGetUnresolvedChangeRequests()
    {
        $changeRequests = $this->subject->getUnresolvedChangeRequests();
        $this->assertCount(1, $changeRequests);
        $this->assertFalse($changeRequests->first()->isResolved());
    }

    public function testGetMonsters()
    {
        $this->assertCount(4, $this->subject->getMonsters());
    }

    public function testGetCommonMonsters()
    {
        $commonMonsters = $this->subject->getCommonMonsters();
        $this->assertCount(2, $commonMonsters);
        foreach ($commonMonsters as $commonMonster) {
            $this->assertFalse($commonMonster->getIsUnique());
        }
    }

    public function testGetBossMonsters()
    {
        $bossMonsters = $this->subject->getBossMonsters();
        $this->assertCount(2, $bossMonsters);
        foreach ($bossMonsters as $bossMonster) {
            $this->assertTrue($bossMonster->getIsUnique());
        }
    }

    public function testSetMonsters()
    {
        $this->subject->setMonsters(new ArrayCollection([$this->makeBossMonster()]));

        $this->assertCount(1, $this->subject->getMonsters());
        $this->assertCount(0, $this->subject->getCommonMonsters());
        $this->assertCount(1, $this->subject->getBossMonsters());
    }

    public function testSetBossMonsters()
    {
        $this->subject->setBossMonsters(new ArrayCollection([$this->makeBossMonster()]));

        $this->assertCount(3, $this->subject->getMonsters());
        $this->assertCount(2, $this->subject->getCommonMonsters());
        $this->assertCount(1, $this->subject->getBossMonsters());
    }

    public function testSetCommonMonsters()
    {
        $this->subject->setCommonMonsters(new ArrayCollection([$this->makeCommonMonster()]));

        $this->assertCount(3, $this->subject->getMonsters());
        $this->assertCount(1, $this->subject->getCommonMonsters());
        $this->assertCount(2, $this->subject->getBossMonsters());
    }

    public function testAddMonster()
    {
        $this->subject->addMonster($this->makeCommonMonster());

        $this->assertCount(5, $this->subject->getMonsters());
        $this->assertCount(3, $this->subject->getCommonMonsters());
        $this->assertCount(2, $this->subject->getBossMonsters());
    }

    public function testEdition()
    {
        $this->subject->setEdition(null);
        $this->assertSame(null, $this->subject->getEdition());
        $edition = new Edition();
        $this->subject->setEdition($edition);
        $this->assertSame($edition, $this->subject->getEdition());
    }

    public function testPublisher()
    {
        $this->subject->setPublisher(null);
        $this->assertSame(null, $this->subject->getPublisher());
        $publisher = new Publisher();
        $this->subject->setPublisher($publisher);
        $this->assertSame($publisher, $this->subject->getPublisher());
    }

    public function testSetting()
    {
        $this->subject->setSetting(null);
        $this->assertSame(null, $this->subject->getSetting());
        $setting = new Setting();
        $this->subject->setSetting($setting);
        $this->assertSame($setting, $this->subject->getSetting());
    }

    private function makeCommonMonster(): Monster
    {
        $monster = new Monster();
        $monster->setIsUnique(false);

        return $monster;
    }

    private function makeBossMonster(): Monster
    {
        $monster = new Monster();
        $monster->setIsUnique(true);

        return $monster;
    }

    private function makeChangeRequest(bool $resolved): ChangeRequest
    {
        $changeRequest = new ChangeRequest();
        $changeRequest->setResolved($resolved);

        return $changeRequest;
    }
}
