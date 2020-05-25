<?php

namespace AppBundle\Listener;

use AppBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionListener
{
    /**
     * @var TokenStorageInterface
     */
    var $tokenStorage;

    /**
     * @var RouterInterface
     */
    var $router;

    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!($exception instanceof AccessDeniedException)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            // User is logged in
            return;
        }

        $request = $event->getRequest();
        if (!$event->isMasterRequest() || $request->isXmlHttpRequest()) {
            return;
        }

        $session = $request->getSession();
        $session->getFlashBag()->add('warning', 'You must login to use this feature.');
    }
}
