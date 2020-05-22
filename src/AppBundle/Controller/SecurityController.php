<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"}) Post requests are intercepted by Symfony but must be allowed here
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param UserInterface|null $user
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils, UserInterface $user = null, Request $request)
    {
        if ($user) {
            $this->addFlash('warning', "You are already logged in.");

            return $this->redirectToRoute('homepage');
        }

        if ($request->cookies->get('sf_redirect')) {
            $this->addFlash('warning', 'You must login to use this feature.');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('shared/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }
}
