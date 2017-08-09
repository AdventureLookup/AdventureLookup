<?php


namespace Tests;

use Gedmo\Blameable\BlameableListener;
use Stof\DoctrineExtensionsBundle\EventListener\BlameListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Adds the new method ->setUserValue() to manually set the user during tests.
 */
class ConfigurableBlameListener extends BlameListener
{
    /**
     * @var BlameableListener
     */
    private $myBlameableListener;

    public function __construct(BlameableListener $blameableListener, $tokenStorage = null, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->myBlameableListener = $blameableListener;

        parent::__construct($blameableListener, $tokenStorage, $authorizationChecker);
    }

    public function setUserValue($user)
    {
        $this->myBlameableListener->setUserValue($user);
    }
}
