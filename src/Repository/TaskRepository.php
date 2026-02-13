<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByUserOrderedByDate(User $user)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.isCompleted', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
