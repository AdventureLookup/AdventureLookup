<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Tests\Fixtures\UserData;
use Tests\WebTestCase;

class PasswordResetControllerTest extends WebTestCase
{
    const USERNAME = 'User #1';

    const NEW_PASSWORD = 'new-password';

    const VALID_RESET_TOKEN = 'reset-token';

    const INVALID_RESET_TOKEN = 'foo-bar';

    public function testFullWorkflow()
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession();

        $session->visit('/login');
        $page = $session->getPage();
        $page->findLink('go here to reset it')->click();

        $page->fillField('request_password_reset_email', 'user1@example.com');

        // The following block temporarily enables the Profiler and disables following redirects.
        // This is necessary to check whether or not an email was sent.
        /** @var Client $client */
        $client = $session->getDriver()->getClient();
        $client->enableProfiler();
        $client->followRedirects(false);
        $page->findButton('Send Password Reset Link')->click();
        $resetPasswordUrl = $this->verifyPasswordResetEmailSent($client);
        $client->followRedirect();
        $client->followRedirects(true);

        $this->assertTrue($page->hasContent('email with a password reset link was sent'));

        $session->visit($resetPasswordUrl);
        $page->fillField('do_password_reset_plainPassword_first', self::NEW_PASSWORD);
        $page->fillField('do_password_reset_plainPassword_second', self::NEW_PASSWORD);
        $page->findButton('Save New Password')->click();

        $this->assertTrue($page->hasContent('Your password was changed'));
        $page->fillField('username', self::USERNAME);
        $page->fillField('password', self::NEW_PASSWORD);
        $page->findButton('Login')->click();

        $session->visit('/profile');
        $this->assertTrue($page->hasContent('Your username is '.self::USERNAME));
    }

    /**
     * @return string The password reset link extracted from the email
     */
    private function verifyPasswordResetEmailSent(Client $client): string
    {
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertContains('Password Reset', $message->getSubject());
        $this->assertEquals('noreply@adventurelookup.com', key($message->getFrom()));
        $this->assertEquals('user1@example.com', key($message->getTo()));
        $this->assertContains(
            self::USERNAME,
            $message->getBody()
        );

        preg_match('#http://localhost/reset-password/reset/[\w-_]{40,}#', $message->getBody(), $matches);
        $this->assertCount(1, $matches);

        return $matches[0];
    }

    public function testRedirectIfLoggedIn()
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession(true);

        $session->visit('/reset-password/request');
        $this->assertPath($session, '/profile/');
        $session->visit('/reset-password/reset/foo-bar');
        $this->assertPath($session, '/profile/');
    }

    public function testIfResetTokenIsValidated()
    {
        $this->loadFixtures([]);
        $session = $this->makeSession();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = new User();
        $user->setUsername('User 1');
        $user->setEmail('user1@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPlainPassword('user1');
        $user->setIsActive(true);
        $user->setPasswordResetToken(self::VALID_RESET_TOKEN);
        $em->persist($user);
        $em->flush();

        $session->visit('/reset-password/reset/'.self::INVALID_RESET_TOKEN);
        $this->assertPath($session, '/reset-password/request');
        $this->assertTrue($session->getPage()->hasContent('Invalid password reset token'));

        $session->visit('/reset-password/reset/'.self::VALID_RESET_TOKEN);
        $this->assertPath($session, '/reset-password/request');
        $this->assertContains('try to request a new password reset link', $session->getPage()->getContent());

        $user->setPasswordResetRequestedAt(new \DateTime('61 minutes ago'));
        $em->flush();
        $session->visit('/reset-password/reset/'.self::VALID_RESET_TOKEN);
        $this->assertPath($session, '/reset-password/request');
        $this->assertContains('reset link is no longer valid', $session->getPage()->getContent());

        $user->setPasswordResetRequestedAt(new \DateTime('55 minutes ago'));
        $em->flush();
        $session->visit('/reset-password/reset/'.self::VALID_RESET_TOKEN);
        $this->assertContains('new account password', $session->getPage()->getContent());
    }
}
