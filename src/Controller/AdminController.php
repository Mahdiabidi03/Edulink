<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        \App\Repository\MatiereRepository $matiereRepo,
        \App\Repository\CoursRepository $coursRepo,
        \App\Repository\UserRepository $userRepo,
        \App\Repository\TransactionRepository $transactionRepo
    ): Response {
        $pendingMatieres = $matiereRepo->findBy(['status' => 'PENDING']);
        $pendingCourses = $coursRepo->findBy(['status' => 'PENDING']);

        $userStats = $userRepo->getUserStatistics();
        $totalXp = $transactionRepo->getTotalPointsEarned();
        $dailyPoints = $transactionRepo->getPointsEarnedToday();
        $activeUsers = $transactionRepo->countActiveUsersToday();
        $recentTransactions = $transactionRepo->findBy([], ['date' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'pendingMatieres' => $pendingMatieres,
            'pendingCourses' => $pendingCourses,
            'userStats' => $userStats,
            'totalXp' => $totalXp,
            'dailyPoints' => $dailyPoints,
            'activeUsers' => $activeUsers,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    #[Route('/admin/events', name: 'admin_events')]
    public function events(Request $request, \App\Repository\EventRepository $eventRepository): Response
    {
        $filters = [
            'search' => $request->query->get('q'),
            'dateFilter' => $request->query->get('filter'),
        ];
        $sort = $request->query->get('sort', 'dateStart');
        $direction = $request->query->get('direction', 'DESC');

        $events = $eventRepository->findByFilter($filters, $sort, $direction);
        $stats = $eventRepository->getEventStats();

        return $this->render('admin/events.html.twig', [
            'events' => $events,
            'stats' => $stats,
            'current_filters' => $filters,
            'current_sort' => $sort,
            'current_direction' => $direction,
        ]);
    }

    #[Route('/admin/events/preview', name: 'admin_event_preview')]
    public function adminEventPreview(Request $request, \App\Repository\EventRepository $eventRepository): Response
    {
        $filter = $request->query->get('filter', 'all');
        $search = $request->query->get('search');

        $qb = $eventRepository->createQueryBuilder('e')
            ->orderBy('e.dateStart', 'DESC');

        if ($search) {
            $qb->andWhere('e.title LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($filter === 'upcoming') {
            $qb->andWhere('e.dateStart > :now')
                ->setParameter('now', new \DateTime());
        }

        $events = $qb->getQuery()->getResult();

        return $this->render('admin/event_preview.html.twig', [
            'events' => $events,
            'current_filter' => $filter,
        ]);
    }

    #[Route('/admin/challenges', name: 'admin_challenges')]
    public function challenges(): Response
    {
        return $this->redirectToRoute('app_challenge_admin_index');
    }

    // Assistance route is handled by App\Controller\Admin\AssistanceController


}
