<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\User;
use AppBundle\Form\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="account")
     * @Method("GET")
     */
    public function overviewAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $adventures = $em->getRepository(Adventure::class)->findBy([
            'createdBy' => $user->getUsername()
        ]);
        // TODO: Enable when change requests are merged.
        $changeRequests = []; /*$em->getRepository()->findBy([
            'createdBy' => $user->getUsername(),
            'resolved' => false,
        ]);*/
        return $this->render('account/overview.html.twig', [
            'user' => $user,
            'changeRequests' => $changeRequests,
            'adventures' => $adventures,
        ]);
    }

    /**
     * @Route("/change-password", name="change_password")
     *
     * @param Request $request
     * @return Response
     */
    public function changePasswordAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // We need to set the password to null to trigger the lifecycle hooks to encode the new plain password.
            // These are only triggered if a mapped property of the entity changes.
            $user->setPassword(null);

            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();

            $this->addFlash('success', 'Your password was changed.');

            return $this->redirectToRoute('account');
        }

        return $this->render('account/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
