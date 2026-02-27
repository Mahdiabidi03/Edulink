<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array Returns statistics about users
     */
    public function getUserStatistics(): array
    {
        $qb = $this->createQueryBuilder('u');
        $totalUsers = $qb->select('count(u.id)')->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('u');
        $totalStudents = $qb->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_STUDENT"%')
            ->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('u');
        $totalTutors = $qb->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_TUTOR"%')
            ->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('u');
        $totalAdmins = $qb->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('u');
        $totalXp = $qb->select('sum(u.walletBalance)')->getQuery()->getSingleScalarResult();

        // Determine top role
        $counts = [
            'Student' => $totalStudents,
            'Tutor' => $totalTutors,
            'Admin' => $totalAdmins
        ];
        arsort($counts);
        $topRole = array_key_first($counts);

        return [
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalTutors' => $totalTutors,
            'totalAdmins' => $totalAdmins,
            'totalXp' => $totalXp ?? 0,
            'topRole' => $topRole
        ];
    }

    /**
     * @return User[] Returns an array of User objects based on filters
     */
    public function findByFilter(array $filters = [], string $sort = 'id', string $direction = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('u');

        if (!empty($filters['search'])) {
            $qb->andWhere('u.email LIKE :search OR u.fullName LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['role'])) {
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%"' . $filters['role'] . '"%');
        }

        // Allowed sort fields to prevent SQL injection
        $allowedSorts = ['id', 'email', 'fullName', 'xp'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('u.' . $sort, $direction);
        } else {
            $qb->orderBy('u.id', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
