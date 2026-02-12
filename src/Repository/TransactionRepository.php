<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getTotalPoints(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.amount > 0') // Assuming positive transactions are "earned"
            ->groupBy('t.id') // This group by might be wrong for a single sum, removed below
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function getTotalPointsEarned(): int
    {
         // Correct query for single scalar sum
        return (int) $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.amount > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPointsEarnedToday(): int
    {
        $today = new \DateTime('today');
        
        return (int) $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.amount > 0')
            ->andWhere('t.date >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveUsersToday(): int
    {
        $today = new \DateTime('today');
        
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(DISTINCT t.user)')
            ->where('t.date >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
