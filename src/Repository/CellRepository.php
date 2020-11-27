<?php

namespace App\Repository;

use App\Entity\Cell;
use App\Entity\Sheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findAllByUserAndSheetAndRange(UserInterface $user, Sheet $sheet, string $left, string $top, string $right, string $bottom): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->join('c.sheet', 's')
            ->andWhere('s.owner = :user')
            ->andWhere('c.sheet = :sheet')
            ->andWhere($qb->expr()->between('c.row', ':top', ':bottom'))
            ->andWhere($qb->expr()->between('c.col', ':left', ':right'))
            ->setParameter('user', $user)
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

    public function findOneByUserAndSheetAndCoordinates(UserInterface $user, Sheet $sheet, string $row, string $col): ?Cell
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->join('c.sheet', 's')
            ->andWhere('s.owner = :user')
            ->andWhere('c.sheet = :sheet')
            ->andWhere('c.row = :row')
            ->andWhere('c.col = :col')
            ->setParameter('user', $user)
            ->setParameter('sheet', $sheet)
            ->setParameter('row', $row)
            ->setParameter('col', $col)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function calculateSumByUserAndSheetAnd1dRange(UserInterface $user, Sheet $sheet, string $rangeKind, int $rangeIndex): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        return (float)$this->createQueryBuilder('c')
                           ->join('c.sheet', 's')
                           ->andWhere('s.owner = :user')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('SUM(c.value) as result')
                           ->setParameter('user', $user)
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function calculateAverageByUserAndSheetAnd1dRange(UserInterface $user, Sheet $sheet, string $rangeKind, int $rangeIndex): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        return (float)$this->createQueryBuilder('c')
                           ->join('c.sheet', 's')
                           ->andWhere('s.owner = :user')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('AVG(c.value) as result')
                           ->setParameter('user', $user)
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function calculatePercentileByUserAndSheetAnd1dRangeAndParameter(UserInterface $user, Sheet $sheet, string $rangeKind, int $rangeIndex, float $parameter): float
    {
        $rangeKind = strtolower($rangeKind);
        if (!in_array($rangeKind, ['col', 'row'])) {
            throw new \InvalidArgumentException('Invalid range kind');
        }

        $cnt = (int)$this->createQueryBuilder('c')
                         ->join('c.sheet', 's')
                         ->andWhere('s.owner = :user')
                         ->andWhere('c.sheet = :sheet')
                         ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                         ->select('COUNT(c) as result')
                         ->setParameter('user', $user)
                         ->setParameter('rangeIndex', $rangeIndex)
                         ->setParameter('sheet', $sheet)
                         ->getQuery()
                         ->getSingleScalarResult();

        $num = abs(round(($cnt - 1) * (1 - $parameter) / 100));

        return (float)$this->createQueryBuilder('c')
                           ->join('c.sheet', 's')
                           ->andWhere('s.owner = :user')
                           ->andWhere('c.sheet = :sheet')
                           ->andWhere(sprintf("c.%s = :rangeIndex", $rangeKind))
                           ->select('c.value as result')
                           ->setParameter('user', $user)
                           ->setParameter('rangeIndex', $rangeIndex)
                           ->setParameter('sheet', $sheet)
                           ->orderBy('c.value', 'DESC')
                           ->setFirstResult($num)
                           ->setMaxResults(1)
                           ->getQuery()
                           ->getSingleScalarResult();
    }

    public function getDimensionsByUserAndSheet(UserInterface $user, Sheet $sheet)
    {
        return $this->createQueryBuilder('c')
                    ->join('c.sheet', 's')
                    ->andWhere('s.owner = :user')
                    ->andWhere('c.sheet = :sheet')
                    ->select('MAX(c.row)+1 as totalRows')
                    ->addSelect('MAX(c.col)+1 as totalCols')
                    ->setParameter('user', $user)
                    ->setParameter('sheet', $sheet)
                    ->getQuery()
                    ->getSingleResult();
    }
}
