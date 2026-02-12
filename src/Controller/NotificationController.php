<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    #[Route('/notification/recent', name: 'notification_recent')]
    public function recent(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response('');
        }

        $notifications = $notificationRepository->findBy(
            ['user' => $user, 'isRead' => false],
            ['createdAt' => 'DESC'],
            5 // Limit to 5
        );

        return $this->render('components/_notifications_widget.html.twig', [
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }

    #[Route('/notification/read/{id}', name: 'notification_read', methods: ['POST'])]
    public function markAsRead(Notification $notification, EntityManagerInterface $entityManager): JsonResponse
    {
        // Security check
        if ($notification->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $notification->setIsRead(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
