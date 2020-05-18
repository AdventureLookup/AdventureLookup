<?php

namespace AppBundle\Curation;

use AppBundle\Entity\Adventure;
use AppBundle\Field\Field;
use AppBundle\Repository\AdventureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BulkEditFormHandler
{
    /**
     * @var AdventureRepository
     */
    private $adventureRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->adventureRepository = $em->getRepository(Adventure::class);
    }

    public function handle(Request $request, FormInterface $form, Field $field): int
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return -1;
        }

        $oldValue = $form->get(BulkEditFormProvider::OLD_VALUE)->getData();
        $newValue = $form->get(BulkEditFormProvider::NEW_VALUE)->getData();
        if (0 === strlen($newValue)) {
            $newValue = null;
        }

        return $this->adventureRepository->updateField($field, $oldValue, $newValue);
    }
}
