<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(\App\Repository\MatiereRepository $matiereRepo, \App\Repository\CoursRepository $coursRepo): Response
    {
        $pendingMatieres = $matiereRepo->findBy(['status' => 'PENDING']);
        $pendingCourses = $coursRepo->findBy(['status' => 'PENDING']);

        return $this->render('admin/dashboard.html.twig', [
            'pendingMatieres' => $pendingMatieres,
            'pendingCourses' => $pendingCourses,
        ]);
    }

    #[Route('/admin/events', name: 'admin_events')]
    public function events(): Response
    {
        return $this->render('admin/events.html.twig');
    }

    #[Route('/admin/challenges', name: 'admin_challenges')]
    public function challenges(): Response
    {
        return $this->render('admin/challenges.html.twig');
    }

    #[Route('/admin/assistance', name: 'admin_assistance')]
    public function assistance(): Response
    {
        return $this->render('admin/assistance.html.twig');
    }


}
