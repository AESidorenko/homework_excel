<?php

namespace App\Repository;

use App\Entity\Sheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sheet[]    findAll()
 * @method Sheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sheet::class);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Sheet[]|array
     */
    public function findAllPaginated(int $offset = 0, int $limit = 25): array
    {
        return $this->createQueryBuilder('s')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function getDimensionsBySheet(Sheet $sheet)
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s := sheet')
                    ->select('MAX(s.row)+1 as totalRows')
                    ->addSelect('MAX(s.col)+1 as totalCols')
                    ->setParameter('sheet', $sheet)
                    ->getQuery()
                    ->getResult();
    }
}
