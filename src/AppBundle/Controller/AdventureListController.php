<?php

namespace AppBundle\Controller;

use AppBundle\Curation\BulkEditFormProvider;
use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureList;
use AppBundle\Form\Type\AdventureListType;
use AppBundle\Security\AdventureListVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/profile/lists")
 */
class AdventureListController extends Controller
{
    const FLASH_CREATION = 'A new list has been created.';
    const FLASH_EDIT = 'The list has been updated';

    /**
     * @Route("/", name="adventure_lists_index")
     * @Method("GET")
     * @Template("profile/adventure_list.html.twig")
     *
     * @param EntityManagerInterface $em
     * @param UserInterface $user
     * @return array
     */
    public function indexAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->denyAccessUnlessGranted(
            AdventureListVoter::LIST,
            'adventure_list'
        );

        $lists = $em->getRepository(AdventureList::class)
            ->myLists($user);
        $form = $this->createForm(AdventureListType::class)->createView();

        return [
            'lists' => $lists,
            'form' => $form
        ];
    }

    /**
     * @Route("/", name="adventure_lists_new")
     * @Method("POST")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function newAction(Request $request, EntityManagerInterface $em)
    {
        $list = new AdventureList();
        $this->denyAccessUnlessGranted(AdventureListVoter::CREATE, $list);

        $form = $this->createForm(AdventureListType::class, $list);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($list);
            $em->flush();
            $this->addFlash('success', self::FLASH_CREATION);
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $message = sprintf(
                    "%s: %s",
                    $error->getOrigin()->getName(),
                    $error->getMessage()
                );
                $this->addFlash('danger', $message);
            }
        }

        return $this->redirectToRoute('adventure_lists_index');
    }

    /**
     * @Route("/{id}", name="adventure_lists_show", requirements={"id"="\d+"})
     * @Method("GET")
     * @Template("profile/adventure_show.html.twig")
     *
     * @param AdventureList $list
     * @return array
     */
    public function showAction(AdventureList $list)
    {
        $this->denyAccessUnlessGranted(AdventureListVoter::SHOW, $list);

        $editForm = $this->createForm(AdventureListType::class, $list);
        $deleteForm = $this->createDeleteForm($list);

        return [
            'list' => $list,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="adventure_lists_edit", requirements={"id"="\d+"})
     * @Method("POST")
     *
     * @param AdventureList $list
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function editAction(
        AdventureList $list,
        Request $request,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted(AdventureListVoter::EDIT, $list);

        $form = $this->createForm(AdventureListType::class, $list);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($list);
            $em->flush();
            $this->addFlash('success', self::FLASH_EDIT);
        } else {
            $this->addFormErrorsAsFlashes($form);
        }

        return $this->redirectToRoute('adventure_lists_show', [
            'id' => $list->getId()
        ]);
    }

    /**
     * @Route("/{list_id}/{adventure_id}",
     *     name="adventure_lists_toggle_contains_adventure",
     *     requirements={"list_id" = "\d+", "adventure_id" = "\d+"},
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("PATCH")
     * @ParamConverter("adventure", options={"id" = "adventure_id"})
     * @ParamConverter("list", options={"id" = "list_id"})
     *
     * @param Adventure $adventure
     * @param AdventureList $list
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function toggleContainsAdventureAction(
        Adventure $adventure,
        AdventureList $list,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted(AdventureListVoter::EDIT, $list);

        if ($list->containsAdventure($adventure)) {
            $list->removeAdventure($adventure);
            $contained = false;
        } else {
            $list->addAdventure($adventure);
            $contained = true;
        }
        $em->flush();

        return new JsonResponse(['contained' => $contained]);
    }

    /**
     * @Route("/{id}", name="adventure_lists_delete", requirements={"id"="\d+"})
     * @Method("DELETE")
     *
     * @param AdventureList $list
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function deleteAction(
        AdventureList $list,
        Request $request,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted(AdventureListVoter::DELETE, $list);

        $deleteForm = $this->createDeleteForm($list);
        $deleteForm->handleRequest($request);
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            throw new BadRequestHttpException();
        }

        $em->remove($list);
        $em->flush();
        $this->addFlash(
            'success',
            sprintf('Your list "%s" has been deleted.', $list->getName())
        );

        return $this->redirectToRoute('adventure_lists_index');
    }

    /**
     * @param AdventureList $list
     * @return Form
     */
    private function createDeleteForm(AdventureList $list)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adventure_lists_delete', [
                'id' => $list->getId()
            ]))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, [
                'label' => 'Delete list forever',
                'attr' => [
                    'class' => 'btn-outline-danger',
                    'onclick' => BulkEditFormProvider::JS_RETURN_CONFIRMATION
                ]
            ])
            ->getForm();
    }

    /**
     * @param Form $form
     */
    private function addFormErrorsAsFlashes(Form $form)
    {
        $errors = $form->getErrors(true);
        foreach ($errors as $error) {
            $message = sprintf(
                "%s: %s",
                $error->getOrigin()->getName(),
                $error->getMessage()
            );
            $this->addFlash('danger', $message);
        }
    }
}
