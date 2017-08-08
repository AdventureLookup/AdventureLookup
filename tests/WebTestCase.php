<?php


namespace Tests;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session;

class WebTestCase extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    const HOST = "http://localhost";

    /**
     * Creates an instance of a Mink Session.
     *
     * If $authentication is set to 'true' it will use the content of
     * 'liip_functional_test.authentication' to log in.
     *
     * $params can be used to pass headers to the client, note that they have
     * to follow the naming format used in $_SERVER.
     * Example: 'HTTP_X_REQUESTED_WITH' instead of 'X-Requested-With'
     *
     * @param bool|array $authentication
     * @param array      $params
     *
     * @return Session
     */
    protected function makeSession($authentication = false, array $params = array())
    {
        $client = $this->makeClient($authentication, $params);
        $mink = new Mink([
            'symfony' => new Session(new BrowserKitDriver($client)),
        ]);

        return $mink->getSession('symfony');
    }

    protected function assertPath(Session $session, string $path)
    {
        $this->assertSame(self::HOST . $path, $session->getCurrentUrl());
    }

    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $result = parent::loadFixtures(
            $classNames,
            $omName,
            $registryName,
            $purgeMode
        );

        $this->runCommand('app:elasticsearch:reindex');

        return $result;
    }

    protected function assertWorkingIndex(Session $session)
    {
        $session->visit('/adventures');
        $this->assertTrue($session->getPage()->hasContent('Adventure search'));
    }
}
