<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CuratedDomain;
use AppBundle\Form\Type\CuratedDomainType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/curation/domains")
 * @Security("is_granted('ROLE_CURATOR')")
 */
class CuratedDomainController extends Controller
{
    /**
     * @Route("/", name="curated_domain_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $curatedDomains = $this->getDoctrine()
            ->getRepository(CuratedDomain::class)
            ->findAll();

        $page = $request->query->getInt('page', 1);
        $curatedDomains = $paginator->paginate($curatedDomains, $page, 20, [
            'defaultSortFieldName' => 'id',
            'defaultSortDirection' => 'asc',
        ]);

        return $this->render('curation/curated_domain/index.html.twig', [
            'curated_domains' => $curatedDomains,
        ]);
    }

    /**
     * @Route("/new", name="curated_domain_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $curatedDomain = new CuratedDomain();
        $form = $this->createForm(CuratedDomainType::class, $curatedDomain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($curatedDomain);
            $entityManager->flush();

            return $this->redirectToRoute('curated_domain_index');
        }

        return $this->render('curation/curated_domain/new.html.twig', [
            'curated_domain' => $curatedDomain,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="curated_domain_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CuratedDomain $curatedDomain): Response
    {
        $form = $this->createForm(CuratedDomainType::class, $curatedDomain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('curated_domain_index');
        }

        return $this->render('curation/curated_domain/edit.html.twig', [
            'curated_domain' => $curatedDomain,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="curated_domain_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CuratedDomain $curatedDomain): Response
    {
        if ($this->isCsrfTokenValid('delete-curated-domain-'.$curatedDomain->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($curatedDomain);
            $entityManager->flush();
        }

        return $this->redirectToRoute('curated_domain_index');
    }
}
