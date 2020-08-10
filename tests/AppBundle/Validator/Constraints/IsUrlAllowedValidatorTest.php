<?php

namespace Tests\AppBundle\Validator\Constraints;

use AppBundle\Entity\CuratedDomain;
use AppBundle\Validator\Constraints\IsUrlAllowed;
use AppBundle\Validator\Constraints\IsUrlAllowedValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IsUrlAllowedValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        $blockedDomains = [
            new CuratedDomain(),
            new CuratedDomain(),
        ];
        $blockedDomains[0]->setDomain('gitlab.com')->setReason('GITLAB');
        $blockedDomains[1]->setDomain('private.github.com')->setReason('PRIVATE');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findBy')
            ->with(['type' => 'B'])
            ->willReturn($blockedDomains);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(CuratedDomain::class)
            ->willReturn($repository);

        return new IsUrlAllowedValidator($em);
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null, new IsUrlAllowed());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new IsUrlAllowed());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType()
    {
        $this->expectException('Symfony\Component\Validator\Exception\UnexpectedTypeException');
        $this->validator->validate(new \stdClass(), new IsUrlAllowed());
    }

    /**
     * @dataProvider getValidUrls
     */
    public function testValidUrls($url)
    {
        $this->validator->validate($url, new IsUrlAllowed());

        $this->assertNoViolation();
    }

    public function getValidUrls()
    {
        return [
            ['https://github.com/AdventureLookup'],
            ['https://example.com/AdventureLookup'],
            ['https://status.github.com/AdventureLookup'],
        ];
    }

    /**
     * @dataProvider getInvalidUrls
     */
    public function testInvalidUrls($url, $reason)
    {
        $constraint = new IsUrlAllowed();

        $this->validator->validate($url, $constraint);

        $this->buildViolation('The url "{{ url }}" is not allowed to be used on AdventureLookup. Reason: {{ reason }}')
            ->setParameter('{{ url }}', $url)
            ->setParameter('{{ reason }}', $reason)
            ->assertRaised();
    }

    public function getInvalidUrls()
    {
        return [
            ['https://gitlab.com/AdventureLookup', 'GITLAB'],
            ['https://blah.gitlab.com/AdventureLookup', 'GITLAB'],
            ['https://private.github.com/AdventureLookup', 'PRIVATE'],
            ['https://super.private.github.com/AdventureLookup', 'PRIVATE'],
        ];
    }
}
