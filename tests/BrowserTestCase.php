<?php

namespace Tests;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class BrowserTestCase extends TestCase
{
    const HOST = 'http://localhost:'.self::PORT;
    const PORT = '8003';

    /** @var Mink */
    private $mink;

    /**
     * Creates an instance of a Mink Session.
     *
     * If $authentication is set to 'true' it will use the content of
     * 'liip_functional_test.authentication' to log in.
     *
     * @param bool|array $authentication
     *
     * @return Session
     */
    protected function makeSession($authentication = false)
    {
        $session = $this->createMinkSession();
        $this->authenticateSession($authentication, $session);

        return $session;
    }

    protected function setUp()
    {
        self::executeCommand('php bin/console doctrine:database:drop --force --env test');
        self::executeCommand('php bin/console doctrine:schema:create --env test');
        self::executeCommand('php bin/console app:elasticsearch:reindex --env test');
    }

    protected function tearDown()
    {
        // This will close the connection to Google Chrome if a mink session
        // was started using createMinkSession().
        $this->mink = null;
    }

    protected function visit(Session $session, string $path)
    {
        $session->visit(self::HOST.$path);
    }

    protected function assertPath(Session $session, string $path)
    {
        $this->assertSame(self::HOST.$path, $session->getCurrentUrl());
    }

    protected function loadFixtures(array $fixtures)
    {
        $fixturePaths = '';
        foreach ($fixtures as $fixture) {
            $reflector = new ReflectionClass($fixture);
            $fixturePaths .= " --fixtures {$reflector->getFileName()}";
        }
        self::executeCommand("php bin/console doctrine:fixtures:load --env test {$fixturePaths}");
        self::executeCommand('php bin/console app:elasticsearch:reindex --env test');
    }

    private static function executeCommand($command)
    {
        $process = new Process($command, __DIR__.DIRECTORY_SEPARATOR.'..');
        $process->mustRun();

        return trim($process->getOutput(), "\n");
    }

    private function createMinkSession(): Session
    {
        $chromeDriver = new ChromeDriver(
            'http://localhost:9222',
            null,
            self::HOST,
            ['downloadBehavior' => 'allow', 'downloadPath' => '/tmp/']);

        // It is important to keep a reference to the Mink object.
        // Otherwise, its destructor is called which closes the websocket connection.
        $this->mink = new Mink([
            'chrome' => new Session($chromeDriver),
        ]);
        $this->mink->setDefaultSessionName('chrome');

        return $this->mink->getSession();
    }

    protected function authenticateSession($authentication, Session $session)
    {
        if ($authentication) {
            if (true === $authentication) {
                $testConfig = Yaml::parse(file_get_contents(__DIR__.'/../app/config/config_test.yml'));
                $authConfig = $testConfig['liip_functional_test']['authentication'];
                $authentication = [
                    'username' => $authConfig['username'],
                    'password' => $authConfig['password'],
                ];
            }
            $session->setBasicAuth($authentication['username'], $authentication['password']);
        }
    }

    protected function assertWorkingIndex(Session $session)
    {
        $this->visit($session, '/adventures');
        $this->assertTrue($session->getPage()->hasContent('A community for lazy dungeon masters'));
    }

    protected function disableFormValidation(Session $session)
    {
        $session->executeScript("
var nodes = document.getElementsByTagName('form');
for (var i = 0; i < nodes.length; ++i) {
  nodes[i].setAttribute('novalidate', 'novalidate');
}");
    }
}
