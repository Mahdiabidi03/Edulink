<?php

namespace App\Repository;

use App\Entity\HelpRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HelpRequest>
 *
 * @method HelpRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpRequest[]    findAll()
 * @method HelpRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpRequest::class);
    }

    /**
     * @return HelpRequest[] Returns an array of HelpRequest objects
     */
    public function findOpenRequests(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('h')
            ->andWhere('h.status = :val')
            ->setParameter('val', 'OPEN')
            ->andWhere('h.isTicket = :isTicket')
            ->setParameter('isTicket', false);

        if (!empty($filters['exclude_student'])) {
            $qb->andWhere('h.student != :me')
               ->setParameter('me', $filters['exclude_student']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('h.title LIKE :search OR h.description LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['min_bounty'])) {
            $qb->andWhere('h.bounty >= :min_bounty')
               ->setParameter('min_bounty', $filters['min_bounty']);
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'oldest':
                $qb->orderBy('h.createdAt', 'ASC');
                break;
            case 'bounty_high':
                $qb->orderBy('h.bounty', 'DESC');
                break;
            case 'bounty_low':
                $qb->orderBy('h.bounty', 'ASC');
                break;
            default: // newest
                $qb->orderBy('h.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function findSupportTickets(): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.isTicket = :isTicket')
            ->setParameter('isTicket', true)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getAssistanceStats(): array
    {
        $em = $this->getEntityManager();

        $totalRequests = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->getQuery()->getSingleScalarResult();

        $openRequests = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.status = :s')->setParameter('s', 'OPEN')
            ->getQuery()->getSingleScalarResult();

        $inProgressRequests = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.status = :s')->setParameter('s', 'IN_PROGRESS')
            ->getQuery()->getSingleScalarResult();

        $closedRequests = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.status = :s')->setParameter('s', 'CLOSED')
            ->getQuery()->getSingleScalarResult();

        $totalBounty = (int) $this->createQueryBuilder('h')
            ->select('COALESCE(SUM(h.bounty), 0)')
            ->getQuery()->getSingleScalarResult();

        $reportedTickets = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.isTicket = :t')->setParameter('t', true)
            ->getQuery()->getSingleScalarResult();

        $totalSessions = (int) $em->createQuery('SELECT COUNT(s.id) FROM App\Entity\Session s')
            ->getSingleScalarResult();

        $activeSessions = (int) $em->createQuery('SELECT COUNT(s.id) FROM App\Entity\Session s WHERE s.isActive = :a')
            ->setParameter('a', true)
            ->getSingleScalarResult();

        $totalMessages = (int) $em->createQuery('SELECT COUNT(m.id) FROM App\Entity\Message m')
            ->getSingleScalarResult();

        $toxicMessages = (int) $em->createQuery('SELECT COUNT(m.id) FROM App\Entity\Message m WHERE m.isToxic = :t')
            ->setParameter('t', true)
            ->getSingleScalarResult();

        $totalReviews = (int) $em->createQuery('SELECT COUNT(r.id) FROM App\Entity\Review r')
            ->getSingleScalarResult();

        $avgRating = $totalReviews > 0
            ? round((float) $em->createQuery('SELECT AVG(r.rating) FROM App\Entity\Review r')->getSingleScalarResult(), 1)
            : 0;

        return [
            'totalRequests' => $totalRequests,
            'openRequests' => $openRequests,
            'inProgressRequests' => $inProgressRequests,
            'closedRequests' => $closedRequests,
            'totalBounty' => $totalBounty,
            'reportedTickets' => $reportedTickets,
            'totalSessions' => $totalSessions,
            'activeSessions' => $activeSessions,
            'totalMessages' => $totalMessages,
            'toxicMessages' => $toxicMessages,
            'resolutionRate' => $totalRequests > 0 ? round(($closedRequests / $totalRequests) * 100) : 0,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
        ];
    }
}
