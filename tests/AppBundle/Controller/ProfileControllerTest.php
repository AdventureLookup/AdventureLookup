<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use Tests\Fixtures\ProfileTestData;
use Tests\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    const LOGIN_URL = '/login';

    const PROFILE_URL = '/profile/';

    const CHANGE_PASSWORD_URL = '/profile/change-password';

    const ERR_NOT_BLANK = 'This value should not be blank.';

    const ERR_WRONG_CURRENT_PW = 'Wrong value for your current password.';
    const ERR_NEW_PASSWORDS_DONT_MATCH = "The two new password fields don't match.";
    const ERR_TOO_LONG = 'This value is too long. It should have 72 characters or less.';

    public function testRedirectIfNotLoggedIn()
    {
        $session = $this->makeSession();
        $session->visit(self::PROFILE_URL);
        $this->assertPath($session, self::LOGIN_URL);

        $session->visit(self::CHANGE_PASSWORD_URL);
        $this->assertPath($session, self::LOGIN_URL);
    }

    public function testOverviewHasUserInfo()
    {
        $referenceRepository = $this->loadFixtures([ProfileTestData::class])->getReferenceRepository();
        /** @var User $user */
        $user = $referenceRepository->getReference('user-1');
        $session = $this->makeSession(true);
        $session->visit(self::PROFILE_URL);
        $page = $session->getPage();
        $this->assertTrue($page->hasContent("Your username is {$user->getUsername()}"));
        $this->assertTrue($page->hasContent($user->getEmail()));
        $this->assertTrue($page->hasContent('You currently have the following roles: User'));
    }

    /**
     * @dataProvider adventureDataProvider
     */
    public function testOverviewOnlyDisplaysOwnAdventures(string $reference, bool $shouldDisplay, int $numPendingChangeRequests)
    {
        $referenceRepository = $this->loadFixtures([ProfileTestData::class])->getReferenceRepository();
        $session = $this->makeSession(true);
        $session->visit(self::PROFILE_URL);
        $page = $session->getPage();

        /** @var Adventure $adventure */
        $adventure = $referenceRepository->getReference($reference);
        $linkToAdventure = $page->findLink($adventure->getTitle());

        if (!$shouldDisplay) {
            $this->assertNull($linkToAdventure);
        } else {
            if ($numPendingChangeRequests > 0) {
                $this->assertContains("{$numPendingChangeRequests} pending change request(s)", $linkToAdventure->getText());
            } else {
                $this->assertNotContains('pending change request(s)', $linkToAdventure->getText());
            }
            $linkToAdventure->click();
            $this->assertTrue($page->hasContent($adventure->getTitle()));
        }
    }

    /**
     * @dataProvider changeRequestDataProvider
     */
    public function testOverviewOnlyDisplaysOwnChangeRequests(string $reference, bool $shouldDisplay)
    {
        $referenceRepository = $this->loadFixtures([ProfileTestData::class])->getReferenceRepository();
        $session = $this->makeSession(true);
        $session->visit(self::PROFILE_URL);
        $page = $session->getPage();

        /** @var ChangeRequest $changeRequest */
        $changeRequest = $referenceRepository->getReference($reference);
        $linkToChangeRequest = $page->findById("change-request-{$changeRequest->getId()}");

        if (!$shouldDisplay) {
            $this->assertNull($linkToChangeRequest);
        } else {
            $linkToChangeRequest->click();
            $this->assertTrue($page->hasContent($changeRequest->getAdventure()->getTitle()));
            $this->assertTrue($page->hasContent($changeRequest->getComment()));
        }
    }

    /**
     * @dataProvider reviewDataProvider
     */
    public function testOverviewOnlyDisplaysOwnReviews(string $reference, bool $shouldDisplay)
    {
        $referenceRepository = $this->loadFixtures([ProfileTestData::class])->getReferenceRepository();
        $session = $this->makeSession(true);
        $session->visit(self::PROFILE_URL);
        $page = $session->getPage();

        /** @var Review $review */
        $review = $referenceRepository->getReference($reference);
        $linkToReview = $page->findById("review-{$review->getId()}");

        if (!$shouldDisplay) {
            $this->assertNull($linkToReview);
        } else {
            $linkToReview->click();
            $this->assertTrue($page->hasContent($review->getAdventure()->getTitle()));
            $this->assertTrue($page->hasContent($review->getComment()));
        }
    }

    public function testChangePasswordFormWithValidData()
    {
        $this->loadFixtures([ProfileTestData::class]);
        $session = $this->makeSession(true);
        $page = $this->submitChangePasswordForm($session, 'user1', 'new-password', 'new-password');

        $this->assertTrue($page->hasContent('Your password was changed.'));

        $container = $this->getContainer();
        $user = $container->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'User #1']);

        $encoder = $container->get('security.password_encoder');
        $newPassword = 'new-password';
        $this->assertTrue($encoder->isPasswordValid($user, $newPassword));
    }

    /**
     * @dataProvider invalidChangePasswordDataProvider
     */
    public function testChangePasswordFormWithInvalidData(string $currentPassword, string $newPassword1, string $newPassword2, array $expectedErrors)
    {
        $this->loadFixtures([ProfileTestData::class]);
        $session = $this->makeSession(true);
        $page = $this->submitChangePasswordForm($session, $currentPassword, $newPassword1, $newPassword2);

        $errorMessages = $page->findAll('css', '.form-error-message');
        $this->assertCount(array_sum($expectedErrors), $errorMessages);

        foreach ($expectedErrors as $expectedError => $count) {
            $this->assertCount($count,
                $page->findAll('css', '.form-error-message:contains("'.$expectedError.'")'));
        }
    }

    private function submitChangePasswordForm(Session $session, string $currentPassword, string $newPassword1, string $newPassword2): DocumentElement
    {
        $session->visit(self::CHANGE_PASSWORD_URL);
        $page = $session->getPage();
        $this->assertTrue($page->hasContent('Change Password'));

        $page->fillField('change_password[currentPassword]', $currentPassword);
        $page->fillField('change_password[plainPassword][first]', $newPassword1);
        $page->fillField('change_password[plainPassword][second]', $newPassword2);
        $page->findButton('Change Password')->click();

        return $page;
    }

    public function adventureDataProvider()
    {
        return [
            ['your-adventure', false, -1],
            ['my-adventure-1', true, 1],
            ['my-adventure-2', true, 0],
            ['my-adventure-3', true, 0],
        ];
    }

    public function changeRequestDataProvider()
    {
        return [
            ['your-unresolved-change-request', false],
            ['my-unresolved-change-request', true],
            ['my-resolved-change-request', true],
        ];
    }

    public function reviewDataProvider()
    {
        return [
            ['your-thumbs-down-review', false],
            ['my-thumbs-up-review', true],
        ];
    }

    public function invalidChangePasswordDataProvider()
    {
        $validNewPassword = '12345678';
        $invalidNewPassword = str_repeat('a', 73);

        return [
            ['', '', '', [self::ERR_NOT_BLANK => 2, self::ERR_WRONG_CURRENT_PW => 1]],
            ['', $validNewPassword, $validNewPassword, [self::ERR_NOT_BLANK => 1, self::ERR_WRONG_CURRENT_PW => 1]],
            ['', $validNewPassword, $validNewPassword, [self::ERR_NOT_BLANK => 1, self::ERR_WRONG_CURRENT_PW => 1]],
            ['INCORRECT', $validNewPassword, $validNewPassword, [self::ERR_WRONG_CURRENT_PW => 1]],
            ['user1', '', '', [self::ERR_NOT_BLANK => 1]],
            ['user1', '', 'a', [self::ERR_NEW_PASSWORDS_DONT_MATCH => 1]],
            ['user1', 'a', '', [self::ERR_NEW_PASSWORDS_DONT_MATCH => 1]],
            ['user1', 'a', 'b', [self::ERR_NEW_PASSWORDS_DONT_MATCH => 1]],
            ['user1', $invalidNewPassword, $invalidNewPassword, [self::ERR_TOO_LONG => 1]],
        ];
    }
}
