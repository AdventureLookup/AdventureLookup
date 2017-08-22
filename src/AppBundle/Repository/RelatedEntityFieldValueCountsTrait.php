<?php


namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

trait RelatedEntityFieldValueCountsTrait
{
    /**
     * Get all distinct values and their usage counts for a certain field. Will ignore NULL values
     *
     * @param string $field
     * @param string|null $additionalWhereCondition
     *
     * @return array Array of arrays containing the 'value' and 'count'
     */
    public function getFieldValueCounts(string $field, string $additionalWhereCondition = null): array
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('tbl');

        $field = 'tbl.' . $field;
        $qb
            ->join('tbl.adventures', 'a')
            ->select($field)
            ->addSelect('tbl.id')
            ->addSelect($qb->expr()->count('a.id') . ' AS _cnt')
            ->where($qb->expr()->isNotNull($field))
            ->groupBy($field)
            ->addGroupBy('tbl.id')
            ->orderBy($qb->expr()->asc($field));
        if ($additionalWhereCondition !== null) {
            $qb->andWhere($additionalWhereCondition);
        }

        $results = $qb->getQuery()->getArrayResult();

        return array_map(function ($result) {
            return [
                'value' => current($result),
                'id' => $result['id'],
                'count' => (int)$result['_cnt'],
            ];
        }, $results);
    }
}
