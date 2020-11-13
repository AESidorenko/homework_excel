<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $order
     * @param int    $offset
     * @param int    $limit
     * @return User[]
     */
    public function findAllOrderedPaginated(string $order = 'id', int $offset = 0, int $limit = 25): array
    {
        $orderRule = sprintf('u.%s', in_array($order, ['id', 'username']) ? $order : 'id');

        return $this->createQueryBuilder('u')
                    ->orderBy($orderRule, 'ASC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }
}
