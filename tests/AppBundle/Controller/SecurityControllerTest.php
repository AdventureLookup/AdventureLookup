<?php

namespace Tests\AppBundle\Controller;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use Tests\Fixtures\UserData;
use Tests\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @dataProvider invalidLoginProvider
     */
    public function testLoginWithInvalidCredentials(string $username, string $password)
    {
        $session = $this->makeSession();
        $page = $this->submitUsernameAndPassword($session, $username, $password);

        $this->assertTrue($page->hasContent('Invalid credentials.'));
    }

    public function testLoginWithValidCredentials()
    {
        $this->loadFixtures([UserData::class]);

        $session = $this->makeSession();
        $this->submitUsernameAndPassword($session, 'User #1', 'user1');

        $this->assertPath($session, '/adventures/');
    }

    public function testRedirectIfAlreadyLoggedIn()
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession(true);
        $session->visit('/login');
        $page = $session->getPage();

        $this->assertTrue($page->hasContent('You are already logged in.'));
    }

    private function submitUsernameAndPassword(Session $session, string $username, string $password): DocumentElement
    {
        $session->visit('/login');
        $page = $session->getPage();

        $this->assertTrue($page->hasContent('Login'));

        $page->fillField('_username', $username);
        $page->fillField('_password', $password);
        $page->findButton('Login')->click();

        return $page;
    }

    /**
     * Provides multiple invalid username / password combinations
     *
     * @return array
     */
    public function invalidLoginProvider()
    {
        return [
            ['', ''],
            ['', '123'],
            ['cmfcmf', ''],
            ['cmfcmf', '123123'],
        ];
    }
}
