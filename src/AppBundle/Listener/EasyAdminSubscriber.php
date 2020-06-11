<?php

namespace AppBundle\Listener;

use AppBundle\Entity\Monster;
use AppBundle\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var TokenStorageInterface
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
            EasyAdminEvents::POST_INITIALIZE => ['denyAccessToUsersForCurators'],
            EasyAdminEvents::PRE_PERSIST => ['makeSureBossMonstersAreUnique'],
        ];
    }

    public function __construct(AccessDecisionManagerInterface $decisionManager, TokenStorageInterface $tokenStorage)
    {
        $this->decisionManager = $decisionManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function makeSureBossMonstersAreUnique(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Monster)) {
            return;
        }
        /** @var Request $request */
        $request = $event->getArgument('request');
        $easyAdminRequestAttributes = $request->attributes->get('easyadmin');
        if ('BossMonster' === $easyAdminRequestAttributes['entity']['name']) {
            $entity->setIsUnique(true);
            $event->setArgument('entity', $entity);
        }
    }

    public function denyAccessToUsersForCurators(GenericEvent $event)
    {
        if (User::class !== $event->getSubject()['class']) {
            return;
        }

        if ($this->decisionManager->decide($this->tokenStorage->getToken(), ['ROLE_ADMIN'])) {
            return;
        }

        throw new AccessDeniedException();
    }
}
