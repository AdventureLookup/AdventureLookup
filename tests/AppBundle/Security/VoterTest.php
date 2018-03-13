<?php


namespace Tests\AppBundle\Security;

use AppBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

abstract class VoterTest extends TestCase
{
    const TOKEN_SECRET = '123';

    /**
     * @return MockObject|AccessDecisionManagerInterface
     */
    protected function createAccessDecisionManagerMock(): MockObject
    {
        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $that = $this;
        $accessDecisionManager
            ->method('decide')
            ->willReturnCallback(function (
                TokenInterface $token,
                array $attributes
            ) use ($that) {
                if (count($attributes) !== 1) {
                    $that->fail('Not implemented in mock');
                }
                /** @var User $user */
                $user = $token->getUser();
                return in_array($attributes[0], $user->getRoles());
            });
        return $accessDecisionManager;
    }

    /**
     * @param $userId
     * @param $roles
     * @return User|MockObject
     */
    protected function createUser(
        $userId,
        $roles
    ): MockObject {
        $myself = $this->createMock(User::class);
        $myself->method('getId')->willReturn($userId);
        $myself->method('getUsername')->willReturn("user " . $userId);
        $myself->method('getRoles')->willReturn($roles);

        return $myself;
    }

    protected function createAnonymousToken(): TokenInterface
    {
        return new AnonymousToken(self::TOKEN_SECRET, 'anon.');
    }

    protected function createUserToken(User $user = null)
    {
        if ($user === null) {
            $user = new User();
        }
        return new UsernamePasswordToken($user, [], 'user_provider');
    }
}
