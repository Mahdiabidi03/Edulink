<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getEventStats(): array
    {
        $now = new \DateTime();

        $totalEvents = (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()->getSingleScalarResult();

        $upcomingEvents = (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.dateStart >= :now')
            ->setParameter('now', $now)
            ->getQuery()->getSingleScalarResult();

        $pastEvents = (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.dateEnd < :now')
            ->setParameter('now', $now)
            ->getQuery()->getSingleScalarResult();

        $totalReservations = (int) $this->getEntityManager()
            ->createQuery('SELECT COUNT(r.id) FROM App\Entity\Reservation r')
            ->getSingleScalarResult();

        return [
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'totalReservations' => $totalReservations,
        ];
    }

    /** @return Event[] */
    public function findByFilter(array $filters = [], string $sort = 'dateStart', string $direction = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.organizer', 'o')
            ->addSelect('o');

        if (!empty($filters['search'])) {
            $qb->andWhere('e.title LIKE :search OR o.email LIKE :search OR o.fullName LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['dateFilter'])) {
            $now = new \DateTime();
            if ($filters['dateFilter'] === 'upcoming') {
                $qb->andWhere('e.dateStart >= :now')->setParameter('now', $now);
            } elseif ($filters['dateFilter'] === 'past') {
                $qb->andWhere('e.dateEnd < :now')->setParameter('now', $now);
            }
        }

        $allowedSorts = ['id', 'title', 'dateStart', 'maxCapacity'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('e.' . $sort, strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('e.dateStart', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
