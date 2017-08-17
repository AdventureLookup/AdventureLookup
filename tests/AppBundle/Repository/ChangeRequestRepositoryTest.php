<?php


namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Repository\ChangeRequestRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Tests\WebTestCase;

class ChangeRequestRepositoryTest extends WebTestCase
{
    /**
     * @var ChangeRequestRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(ChangeRequest::class);

        $fixture = new class extends AbstractFixture {
            public function load(ObjectManager $em)
            {
                $adventure = new Adventure();
                $adventure->setTitle('adventure');
                $em->persist($adventure);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(false);
                $changeRequest->setComment('unresolved');
                $changeRequest->setCuratorRemarks('remarks');
                $changeRequest->setAdventure($adventure);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setComment('resolved');
                $changeRequest->setAdventure($adventure);
                $em->persist($changeRequest);
                $em->flush();
            }
        };
        $this->loadFixtures([get_class($fixture)]);
    }

    public function testUnresolvedChangeRequestsQuery()
    {
        $query = $this->repository->getUnresolvedChangeRequestsQuery();
        $this->assertInstanceOf(Query::class, $query);

        /** @var ChangeRequest[] $results */
        $results = $query->execute();
        $this->assertCount(1, $results);
        $this->assertSame('unresolved', $results[0]->getComment());
    }

    /**
     * @dataProvider resolveParameterProvider
     */
    public function testResolveChangeRequestsById(bool $resolve, string $remarks = null,
                                                  string $expectedRemarks)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var ChangeRequest $changeRequest */
        $changeRequest = $em->getRepository(ChangeRequest::class)->find(1);
        $this->assertFalse($changeRequest->isResolved());

        $this->repository->resolveChangeRequestsByIds($resolve ? [1] : [], $remarks);

        $em->refresh($changeRequest);
        $this->assertSame($resolve, $changeRequest->isResolved());
        $this->assertSame($expectedRemarks, $changeRequest->getCuratorRemarks());
    }

    public function resolveParameterProvider()
    {
        return [
            [false, 'remarks', 'remarks'],
            [true, null, 'remarks'],
            [true, 'new remarks', 'new remarks'],
        ];
    }
}
