<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Review;
use AppBundle\Entity\ReviewVote;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ReviewType;
use AppBundle\Security\ReviewVoter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Route("/review")
 */
class ReviewController extends Controller
{
    /**
     * @Route("/new/{id}", name="review_new")
     * @Method("POST")
     * @ParamConverter()
     *
     * @param Request $request
     * @param Adventure $adventure
     * @return RedirectResponse
     */
    public function newAction(Request $request, Adventure $adventure)
    {
        $this->denyAccessUnlessGranted(ReviewVoter::CREATE, 'review');

        $review = new Review($adventure);
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($review);

            try {
                $em->flush();
                $this->addFlash('success', 'Your review has been saved.');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'It looks like you already created a review for this adventure. Your review has not been saved.');
            }

        } else {
            $this->showErrors($form);
        }

        return $this->redirectToAdventureForReview($review);
    }

    /**
     * @Route("/edit/{id}", name="review_edit")
     * @Method("POST")
     * @ParamConverter()
     *
     * @param Request $request
     * @param Review $review
     * @return RedirectResponse
     */
    public function editAction(Request $request, Review $review)
    {
        $this->denyAccessUnlessGranted(ReviewVoter::EDIT, $review);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($review);
            $em->flush();

            $this->addFlash('success', 'Your review has been edited.');
        } else {
            $this->showErrors($form);
        }

        return $this->redirectToAdventureForReview($review);
    }

    /**
     * @Route("/delete/{id}", name="review_delete")
     * @Method("DELETE")
     * @ParamConverter()
     *
     * @param Review $review
     * @return RedirectResponse
     */
    public function deleteAction(Review $review)
    {
        $this->denyAccessUnlessGranted(ReviewVoter::DELETE, $review);

        $em = $this->getDoctrine()->getManager();
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'Your review has been deleted.');

        return $this->redirectToAdventureForReview($review);
    }

    /**
     * @Route("/vote/{id}", name="review_vote", defaults={"_format": "json"})
     * @Method("POST")
     * @ParamConverter()
     *
     * @param Review $review
     * @param Request $request
     * @return JsonResponse
     */
    public function voteAction(Review $review, Request $request)
    {
        if (!$this->isGranted(ReviewVoter::VOTE, $review)) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }
        if (!$this->isCsrfTokenValid('review_voting', $request->request->get('_token'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $vote = $request->request->getInt('vote');
        if (!in_array($vote, [1, 0, -1], true)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        /** @var User $user */
        $user = $this->getUser();
        $reviewVote = $doctrine
            ->getRepository(ReviewVote::class)
            ->findOneBy([
                'user' => $user->getId(),
                'review' => $review
        ]);

        if (null !== $reviewVote) {
            if ($vote === 0) {
                $em->remove($reviewVote);
            } else {
                $reviewVote->setVote($vote === 1);
            }
        } else {
            if ($vote !== 0) {
                $reviewVote = new ReviewVote($review, $user, $vote === 1);
                $em->persist($reviewVote);
            }
        }
        $em->flush();

        $em->refresh($review);

        return new JsonResponse([
            'upvotes' => $review->countUpvotes(),
            'downvotes' => $review->countDownvotes(),
        ]);
    }

    /**
     * @param Review $review
     * @return RedirectResponse
     */
    private function redirectToAdventureForReview(Review $review): RedirectResponse
    {
        return $this->redirectToRoute('adventure_show',
            ['slug' => $review->getAdventure()->getSlug()]);
    }

    /**
     * @param FormInterface $form
     */
    private function showErrors(FormInterface $form)
    {
        $this->addFlash('danger', 'There was an error with your review.');
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('danger', sprintf(
                "%s: %s",
                $error->getOrigin()->getName(),
                $error->getMessage()));
        }
    }
}
