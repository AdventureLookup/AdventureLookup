<?php


namespace Tests\AppBundle\Curation;

use AppBundle\Curation\BulkEditFormHandler;
use AppBundle\Curation\BulkEditFormProvider;
use AppBundle\Entity\Adventure;
use AppBundle\Field\Field;
use AppBundle\Repository\AdventureRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BulkEditFormHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BulkEditFormHandler
     */
    private $formHandler;

    /**
     * @var Request|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var Field|PHPUnit_Framework_MockObject_MockObject
     */
    private $field;

    /**
     * @var AdventureRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var FormInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $form;

    public function setUp()
    {
        $this->repository = $this->createMock(AdventureRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(Adventure::class)
            ->willReturn($this->repository);

        $this->formHandler = new BulkEditFormHandler($em);

        $this->request = $this->createMock(Request::class);
        $this->field = $this->createMock(Field::class);
        $this->form = $this->createMock(FormInterface::class);
    }

    /**
     * @dataProvider invalidOrNotSubmittedStateProvider
     */
    public function testDoesNothingIfInvalidOrNotSubmitted($isSubmitted, $isValid)
    {
        $this->form->method('isSubmitted')->willReturn($isSubmitted);
        $this->form->method('isValid')->willReturn($isValid);
        $this->repository->expects($this->never())->method('updateField');

        $result = $this->formHandler->handle($this->request, $this->form, $this->field);
        $this->assertSame(-1, $result);
    }

    /**
     * @dataProvider validFormDataProvider
     */
    public function testCallsUpdateFieldIfValidAndSubmitted($oldValue, $newValue, $expectedOldValue, $expectedNewValue, $expectedResult)
    {
        $this->form->method('isSubmitted')->willReturn(true);
        $this->form->method('isValid')->willReturn(true);
        $this->form->method('get')->will($this->returnValueMap([
            [BulkEditFormProvider::OLD_VALUE, $this->formDataMock($oldValue)],
            [BulkEditFormProvider::NEW_VALUE, $this->formDataMock($newValue)],
        ]));
        $this->repository
            ->expects($this->once())
            ->method('updateField')
            ->with($this->field, $expectedOldValue, $expectedNewValue)
            ->willReturn($expectedResult);

        $result = $this->formHandler->handle($this->request, $this->form, $this->field);
        $this->assertSame($expectedResult, $result);
    }

    private function formDataMock($data)
    {
        $formDataMock = $this->createMock(FormInterface::class);
        $formDataMock->method('getData')->willReturn($data);

        return $formDataMock;
    }

    public function invalidOrNotSubmittedStateProvider()
    {
        return [
            [false, false],
            [false, true],
            [true, false],
        ];
    }

    public function validFormDataProvider()
    {
        return [
            ['the old value', 'the new value', 'the old value', 'the new value', 73],
            ['sec. old value', '', 'sec. old value', null, 12],
            ['third old value', null, 'third old value', null, 2],
            ['', null, '', null, 0],
        ];
    }
}
