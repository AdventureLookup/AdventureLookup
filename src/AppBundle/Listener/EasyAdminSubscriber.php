<?php


namespace AppBundle\Listener;

use AppBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccessDecisionManager
     */
    private $decisionManager;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_INITIALIZE => ['denyAccessToUsersForCurators']
        ];
    }

    public function __construct(AccessDecisionManager $decisionManager, TokenStorage $tokenStorage)
    {
        $this->decisionManager = $decisionManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function denyAccessToUsersForCurators(GenericEvent $event)
    {
        if ($event->getSubject()['class'] !== User::class) {
            return;
        }

        if ($this->decisionManager->decide($this->tokenStorage->getToken(), ['ROLE_ADMIN'])) {
            return;
        }

        throw new AccessDeniedException();
    }
}
