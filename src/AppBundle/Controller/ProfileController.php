<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ChangePasswordType;
use Doctrine\ORM\Query\Expr;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/profile")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="profile")
     * @Method("GET")
     *
     * @param UserInterface $user
     * @return Response
     */
    public function overviewAction(UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $adventureRepository = $em->getRepository(Adventure::class);
        $changeRequestRepository = $em->getRepository(ChangeRequest::class);

        $qb = $adventureRepository->createQueryBuilder('a');
        // Get all adventures created by the current user as well as corresponding pending change requests.
        // Sort them by adventures having a change request, then by title
        $adventures = $qb
            ->where($qb->expr()->eq('a.createdBy', ':username'))
            ->leftJoin('a.changeRequests', 'c', Expr\Join::WITH, 'c.resolved = false')
            ->addSelect('c')
            ->orderBy('c.id', 'DESC')
            ->addOrderBy('a.title', 'ASC')
            ->setParameter('username', $user->getUsername())
            ->getQuery()
            ->execute();

        $changeRequests = $changeRequestRepository->findBy([
            'createdBy' => $user->getUsername(),
        ], ['createdAt' => 'DESC', 'resolved' => 'ASC']);

        return $this->render('profile/overview.html.twig', [
            'changeRequests' => $changeRequests,
            'adventures' => $adventures,
        ]);
    }

    /**
     * @Route("/change-password", name="change_password")
     * @Method({"GET", "POST"})
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

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
