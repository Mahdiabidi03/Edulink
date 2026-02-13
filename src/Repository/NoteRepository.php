<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function findByUserOrderedByDate(User $user)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserWithFilters(User $user, ?string $query = null, ?int $categoryId = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user);

        if ($query) {
            $qb->andWhere('n.title LIKE :query OR n.content LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($categoryId) {
            $qb->andWhere('n.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Deprecated tag-related methods removed
}
