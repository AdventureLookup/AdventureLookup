<?php


namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\DoPasswordResetType;
use AppBundle\Form\RequestPasswordResetType;
use AppBundle\Security\TokenGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/reset-password")
 */
class PasswordResetController extends Controller
{
    /**
     * How many minutes a password reset link is valid.
     */
    const PASSWORD_RESET_LINK_TTL = 60;

    /**
     * @Route("/request", name="request_password_reset")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param UserInterface|null $user
     * @return RedirectResponse|Response
     */
    public function requestPasswordResetAction(Request $request, UserInterface $user = null)
    {
        if ($user) {
            return $this->redirectToRoute('profile');
        }

        $form = $this->createForm(RequestPasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user instanceof User) {
                $user
                    ->setPasswordResetRequestedAt(new \DateTime())
                    ->setPasswordResetToken(TokenGenerator::generateToken());
                $em->flush();

                $this->sendPasswordResetMail($user);
            }

            $this->addFlash('success', "If the provided email address is associated with a user account, an email with a password reset link was sent to it.");

            return $this->redirectToRoute('login');
        }

        return $this->render('password_reset/request_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset/{token}", name="do_password_reset")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param string $token
     * @param UserInterface $user
     * @return RedirectResponse|Response
     */
    public function doPasswordResetAction(Request $request, string $token, UserInterface $user = null)
    {
        if ($user) {
            return $this->redirectToRoute('profile');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['passwordResetToken' => $token]);
        if (!$user) {
            $this->addFlash('danger', "Invalid password reset token. Maybe you didn't copy the whole link?");

            return $this->redirectToRoute('request_password_reset');
        }

        $passwordResetRequestedAt = $user->getPasswordResetRequestedAt();
        if (!$passwordResetRequestedAt instanceof \DateTimeInterface) {
            $this->addFlash('danger', "Something went wrong. Please try to request a new password reset link.");

            return $this->redirectToRoute('request_password_reset');
        }

        if (time() > $passwordResetRequestedAt->getTimestamp() + self::PASSWORD_RESET_LINK_TTL * 60) {
            $this->addFlash('danger', "This password reset link is no longer valid. Please generate a new one if you still need to reset your password.");

            return $this->redirectToRoute('request_password_reset');
        }

        $form = $this->createForm(DoPasswordResetType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $user
                ->setPasswordResetRequestedAt(null)
                ->setPasswordResetToken(null);
            $em->flush();

            $this->addFlash('success', "Your password was changed. You can now login using your username and password");

            return $this->redirectToRoute('login');
        }

        return $this->render('password_reset/do_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     */
    private function sendPasswordResetMail(User $user)
    {
        $mailer = $this->get('mailer');
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('noreply@adventurelookup.com')
            ->setTo($user->getEmail())
            ->setSubject('Password Reset for AdventureLookup')
            ->setBody(
                $this->renderView(
                    'emails/reset_password.txt.twig',
                    ['user' => $user, 'ttl' => self::PASSWORD_RESET_LINK_TTL]
                ),
                'text/plain'
            );

        $mailer->send($message);
    }
}
