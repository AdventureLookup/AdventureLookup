<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param UserInterface|null $user
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, SessionInterface $session, TokenStorageInterface $tokenStorage, UserInterface $user = null)
    {
        if ($user) {
            $this->addFlash('warning', "You are already logged in.");

            return $this->redirectToRoute('homepage');
        }

        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // 3) Encoding password - handled through HashPasswordListener

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            $this->addFlash('success', 'Account created. You have been logged in.');
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'shared/register.html.twig',
            array('form' => $form->createView())
        );
    }
}
