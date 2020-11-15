<?php

namespace App\Repository;

use App\Entity\Cell;
use App\Entity\Sheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cell[]    findAll()
 * @method Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cell::class);
    }

    // /**
    //  * @return Cell[] Returns an array of Cell objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cell
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllBySheetAndRange(Sheet $sheet, string $left, string $top, string $right, string $bottom): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->where('c.sheet = :sheet')
                  ->andWhere($qb->expr()->between('c.row', ':top', ':bottom'))
                  ->andWhere($qb->expr()->between('c.col', ':left', ':right'))
                  ->setParameter('sheet', $sheet)
                  ->setParameter('top', $top)
                  ->setParameter('right', $right)
                  ->setParameter('bottom', $bottom)
                  ->setParameter('left', $left)
                  ->orderBy('c.row', 'ASC')
                  ->addOrderBy('c.col', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    public function findOneBySheetAndCoordinates(Sheet $sheet, string $row, string $col): ?Cell
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->andWhere('c.sheet = :sheet')
                  ->andWhere('c.row = :row')
                  ->andWhere('c.col = :col')
                  ->setParameter('sheet', $sheet)
                  ->setParameter('row', $row)
                  ->setParameter('col', $col)
                  ->getQuery()
                  ->getOneOrNullResult();
    }

    public function calculateSumBySheetAnd1dRange(Sheet $sheet, string $rangeKind, int $rangeIndex): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        return (float)$this->createQueryBuilder('c')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('SUM(c.value) as result')
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function calculateAverageBySheetAnd1dRange(Sheet $sheet, string $rangeKind, int $rangeIndex): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        return (float)$this->createQueryBuilder('c')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('AVG(c.value) as result')
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function calculatePercentileBySheetAnd1dRangeAndParameter(Sheet $sheet, string $rangeKind, int $rangeIndex, float $parameter): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        $cnt = (int)$this->createQueryBuilder('c')
                         ->andWhere('c.sheet = :sheet')
                         ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                         ->select('COUNT(c) as result')
                         ->setParameter('rangeIndex', $rangeIndex)
                         ->setParameter('sheet', $sheet)
                         ->getQuery()
                         ->getSingleScalarResult();

        $num = abs(round(($cnt - 1) * (1 - $parameter) / 100));

        return (float)$this->createQueryBuilder('c')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('c.value as result')
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->orderBy('c.value', 'DESC')
                           ->setFirstResult($num)
                           ->setMaxResults(1)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function getDimensionsBySheet(Sheet $sheet)
    {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.sheet = :sheet')
                    ->select('MAX(c.row)+1 as totalRows')
                    ->addSelect('MAX(c.col)+1 as totalCols')
                    ->setParameter('sheet', $sheet)
                    ->getQuery()
                    ->getSingleResult();
    }
}
