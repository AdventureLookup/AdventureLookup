<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\CuratedDomain;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsUrlAllowedValidator extends ConstraintValidator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsUrlAllowed) {
            throw new UnexpectedTypeException($constraint, IsUrlAllowed::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $blockedDomains = $this->em->getRepository(CuratedDomain::class)->findBy([
            'type' => 'B',
        ]);

        foreach ($blockedDomains as $blockedDomain) {
            if ($blockedDomain->matchesUrl($value)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ url }}', $value)
                    ->setParameter('{{ reason }}', $blockedDomain->getReason())
                    ->addViolation();
                break;
            }
        }
    }
}
