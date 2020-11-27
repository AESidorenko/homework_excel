<?php

namespace App\Repository;

use App\Entity\Sheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param UserInterface $user
     * @param int           $offset
     * @param int           $limit
     * @return Sheet[]|array
     */
    public function findAllPaginatedByUserAndOffsetAndLimit(UserInterface $user, int $offset = 0, int $limit = 25): array
    {
        return $this->createQueryBuilder('s')
                    ->where('s.owner = :user')
                    ->setParameter('user', $user)
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function countByUser(UserInterface $user)
    {
        return $this->createQueryBuilder('s')
                    ->select('COUNT(s.id) as totalSheets')
                    ->where('s.owner = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function findOneByUserAndName(UserInterface $user, string $name)
    {
        return $this->createQueryBuilder('s')
                    ->where('s.owner = :user')
                    ->andWhere('s.name = :name')
                    ->setParameter('user', $user)
                    ->setParameter('name', $name)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
