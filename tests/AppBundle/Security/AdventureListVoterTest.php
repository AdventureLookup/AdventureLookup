<?php


namespace Tests\AppBundle\Security;

use AppBundle\Entity\AdventureList;
use AppBundle\Entity\User;
use AppBundle\Security\AdventureListVoter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdventureListVoterTest extends VoterTest
{
    const TOKEN_SECRET = '123';
    const LIST_ATTRIBUTE = 'list';
    const CREATE_ATTRIBUTE = 'create';

    /**
     * @var AdventureListVoter
     */
    private $voter;

    public function setUp()
    {
        $this->voter = new AdventureListVoter();
    }

    /**
     * @dataProvider unsupportedSubjectsAndAttributesDataProvider
     */
    public function testUnsupportedSubjectsAndAttributes($subject, $attribute)
    {
        $result = $this->voter->vote($this->createAnonymousToken(), $subject,
            [$attribute]);
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function unsupportedSubjectsAndAttributesDataProvider()
    {
        return [
            ['a subject', 'delete'],
            ['a subject', 'create'],
            ['adventure_list', 'something'],
            [new AdventureList(), 'something'],
            [new AdventureList(), self::LIST_ATTRIBUTE],
        ];
    }

    /**
     * @dataProvider listAndCreateAttributeDataProvider
     */
    public function testListAndCreateAttribute($attribute, $token, $subject, $expectedResult)
    {
        $result = $this->voter->vote($token, $subject, [$attribute]);
        $this->assertSame($expectedResult, $result);
    }

    public function listAndCreateAttributeDataProvider()
    {
        return [
            [
                self::LIST_ATTRIBUTE,
                $this->createAnonymousToken(),
                'adventure_list',
                VoterInterface::ACCESS_DENIED,
            ],
            [
                self::LIST_ATTRIBUTE,
                $this->createUserToken(),
                'adventure_list',
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                self::CREATE_ATTRIBUTE,
                $this->createAnonymousToken(),
                new AdventureList(),
                VoterInterface::ACCESS_DENIED,
            ],
            [
                self::CREATE_ATTRIBUTE,
                $this->createUserToken(),
                new AdventureList(),
                VoterInterface::ACCESS_GRANTED,
            ],
        ];
    }

    /**
     * @dataProvider otherAttributesDataProvider
     */
    public function testOtherAttributes($token, $subject, $expectedResult)
    {
        foreach (['view', 'edit', 'delete'] as $attribute) {
            $result = $this->voter->vote($token, $subject, [$attribute]);
            $this->assertSame($expectedResult, $result);
        }
    }

    public function otherAttributesDataProvider()
    {
        $userId = 1;

        $myself = $this->createMock(User::class);
        $myself->method('getId')->willReturn($userId++);
        $myAdventureList = $this->createMock(AdventureList::class);
        $myAdventureList->method('getUser')->willReturn($myself);

        $you = $this->createMock(User::class);
        $you->method('getId')->willReturn($userId++);
        $yourAdventureList = $this->createMock(AdventureList::class);
        $yourAdventureList->method('getUser')->willReturn($you);

        return [
            [
                $this->createAnonymousToken(),
                new AdventureList(),
                VoterInterface::ACCESS_DENIED,
            ],
            [
                $this->createUserToken($myself),
                $myAdventureList,
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                $this->createUserToken($myself),
                $yourAdventureList,
                VoterInterface::ACCESS_DENIED,
            ],
        ];
    }
}
