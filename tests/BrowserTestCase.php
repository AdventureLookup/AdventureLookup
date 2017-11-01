<?php


namespace Tests;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Zumba\Mink\Driver\PhantomJSDriver;

class BrowserTestCase extends TestCase
{
    const HOST = "http://localhost:" . self::PORT;
    const PORT = '8003';

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

    protected function visit(Session $session, string $path)
    {
        $session->visit(self::HOST . $path);
    }

    protected function assertPath(Session $session, string $path)
    {
        $this->assertSame(self::HOST . $path, $session->getCurrentUrl());
    }

    protected function loadFixtures(array $fixtures)
    {
        $fixturePaths = '';
        foreach ($fixtures as $fixture) {
            $reflector = new ReflectionClass($fixture);
            $fixturePaths .=  " --fixtures {$reflector->getFileName()}";
        }
        self::executeCommand("php bin/console doctrine:fixtures:load --env test {$fixturePaths}");
        self::executeCommand("php bin/console app:elasticsearch:reindex --env test");
    }

    private static function executeCommand($command)
    {
        $process = new Process($command);

        // Make sure the working directory is the root of the application.
        // Do so by removing anything after 'vendor' in the path to the simple-phpunit script.
        $dir = $_SERVER['SCRIPT_NAME'];
        $testFolderPosition = strrpos($dir, 'vendor');
        if ($testFolderPosition !== null) {
            $process->setWorkingDirectory(substr($dir, 0, $testFolderPosition));
        }

        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput(), "\n");
    }

    private function createMinkSession(): Session
    {
        $mink = new Mink([
            'phantomjs' => new Session(new PhantomJSDriver('http://localhost:8510')),
        ]);
        $mink->setDefaultSessionName('phantomjs');

        return $mink->getSession();
    }

    protected function authenticateSession($authentication, Session $session)
    {
        if ($authentication) {
            if ($authentication === true) {
                $testConfig = Yaml::parse(file_get_contents(__DIR__ . '/../app/config/config_test.yml'));
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
}
