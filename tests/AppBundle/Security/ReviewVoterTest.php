<?php


namespace Tests\AppBundle\Security;

use AppBundle\Entity\Review;
use AppBundle\Security\ReviewVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ReviewVoterTest extends VoterTest
{
    const LIST_ATTRIBUTE = 'list';
    const CREATE_ATTRIBUTE = 'create';

    /**
     * @var ReviewVoter
     */
    private $voter;

    public function setUp(): void    {
        $accessDecisionManager = $this->createAccessDecisionManagerMock();
        $this->voter = new ReviewVoter($accessDecisionManager);
    }

    /**
     * @dataProvider unsupportedSubjectsAndAttributesDataProvider
     */
    public function testUnsupportedSubjectsAndAttributes($subject, $attribute)
    {
        $result = $this->voter->vote(
            $this->createAnonymousToken(),
            $subject,
            [$attribute]);
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function unsupportedSubjectsAndAttributesDataProvider()
    {
        return [
            ['a subject', 'delete'],
            ['a subject', 'create'],
            ['review', 'something'],
            [$this->createReview(), 'something'],
            [$this->createReview(), self::CREATE_ATTRIBUTE],
        ];
    }

    /**
     * @dataProvider createAttributeDataProvider
     */
    public function testCreateAttribute($attribute, $token, $subject, $expectedResult)
    {
        $result = $this->voter->vote($token, $subject, [$attribute]);
        $this->assertSame($expectedResult, $result);
    }

    public function createAttributeDataProvider()
    {
        return [
            [
                self::CREATE_ATTRIBUTE,
                $this->createAnonymousToken(),
                'review',
                VoterInterface::ACCESS_DENIED,
            ],
            [
                self::CREATE_ATTRIBUTE,
                $this->createUserToken(),
                'review',
                VoterInterface::ACCESS_GRANTED,
            ],
        ];
    }

    /**
     * @dataProvider otherAttributesDataProvider
     */
    public function testOtherAttributes($token, $subject, $expectedResult, $attributes)
    {
        foreach ($attributes as $attribute) {
            $result = $this->voter->vote($token, $subject, [$attribute]);
            $this->assertSame($expectedResult, $result);
        }
    }

    public function otherAttributesDataProvider()
    {
        $normalUser = $this->createUser(1, ['ROLE_USER']);
        $normalUserReview = $this->createReview(1);

        $otherUserReview = $this->createReview(2);

        $curatorUser = $this->createUser(3, ['ROLE_CURATOR']);

        return [
            [
                $this->createAnonymousToken(),
                $this->createReview(),
                VoterInterface::ACCESS_DENIED,
                ['edit', 'delete'],
            ],
            [
                $this->createUserToken($normalUser),
                $normalUserReview,
                VoterInterface::ACCESS_GRANTED,
                ['edit', 'delete'],
            ],
            [
                $this->createUserToken($normalUser),
                $otherUserReview,
                VoterInterface::ACCESS_DENIED,
                ['edit', 'delete'],
            ],
            [
                $this->createUserToken($curatorUser),
                $otherUserReview,
                VoterInterface::ACCESS_GRANTED,
                ['delete'],
            ]
        ];
    }

    /**
     * @param int $userId
     * @return Review|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createReview(int $userId = null)
    {
        $review = $this->createMock(Review::class);

        if ($userId !== null) {
            $review->method('getCreatedBy')->willReturn("user " . $userId);
        }

        return $review;
    }
}
