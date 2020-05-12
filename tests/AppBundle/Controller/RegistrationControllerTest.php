<?php

namespace Tests\AppBundle\Controller;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use Tests\Fixtures\UserData;
use Tests\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationWithValidData()
    {
        $username = 'cmfcmf';
        $email = 'cmfcmf@example.com';
        $password = '12345678';

        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession();
        $page = $this->submitForm($session, $username, $email, $password);

        $this->assertTrue($page->hasContent('Account created. You have been logged in.'));
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testRegistrationWithInvalidData(string $username, string $email, string $password, array $expectedErrors)
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession();
        $page = $this->submitForm($session, $username, $email, $password);

        $errorMessages = $page->findAll('css', '.form-error-message');
        $this->assertCount(array_sum($expectedErrors), $errorMessages);

        foreach ($expectedErrors as $expectedError => $count) {
            $this->assertCount($count,
                $page->findAll('css', '.form-error-message:contains("' . $expectedError . '")'));
        }
    }

    public function testRedirectIfAlreadyLoggedIn()
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession(true);
        $session->visit('/register');
        $page = $session->getPage();

        $this->assertTrue($page->hasContent('You are already logged in.'));
    }

    private function submitForm(Session $session, string $username, string $email, string $password): DocumentElement
    {
        $session->visit('/register');
        $page = $session->getPage();

        $this->assertTrue($page->hasContent('Create Account'));

        $page->fillField('user[username]', $username);
        $page->fillField('user[email]', $email);
        $page->fillField('user[plainPassword][first]', $password);
        $page->fillField('user[plainPassword][second]', $password);
        $page->findButton('Register!')->click();

        return $page;
    }

    /**
     * Provides multiple invalid username / password combinations
     *
     * @return array
     */
    public function invalidDataProvider()
    {
        $password = '12345678';
        $tooLongPassword = str_repeat('a', 73);
        $tooLongUsername = str_repeat('a', 26);
        $tooLongEmail = str_repeat('a', 49) . '@example.com';

        return [
            // Empty fields
            ['', '', '', ['This value should not be blank.' => 3]],
            // Invalid email
            ['cmfcmf', 'foo.bar', $password, ['This value is not a valid email address.' => 1]],
            // Password too long
            ['cmfcmf', 'cmfcmf@example.com', $tooLongPassword, ['This value is too long.' => 1]],
            // Username too long
            [$tooLongUsername, 'cmfcmf@example.com', $password, ['This value is too long.' => 1]],
            // Email too long
            ['cmfcmf', $tooLongEmail, $password, ['This value is too long.' => 1]],
            // Duplicate username and email
            ['User #1', 'user1@example.com', $password, ['Username already taken' => 1, 'Email already taken' => 1]],
        ];
    }
}
