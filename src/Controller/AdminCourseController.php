<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Resource;
use App\Form\CoursType;
use App\Form\ResourceType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class AdminCourseController extends AbstractController
{
    #[Route('/', name: 'app_admin_courses')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_categories');
    }

    // 2. CREATE NEW OFFICIAL COURSE
    #[Route('/new', name: 'app_admin_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cours = new Cours();
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Matiere
            $matiereName = $form->get('matiere_name')->getData();
            if ($matiereName) {
                $matiereRepo = $em->getRepository(\App\Entity\Matiere::class);
                $matiere = $matiereRepo->findOneBy(['name' => $matiereName]);

                if (!$matiere) {
                    $matiere = new \App\Entity\Matiere();
                    $matiere->setName($matiereName);
                    $matiere->setStatus('APPROVED'); // Admin created, so approved
                    $em->persist($matiere);
                }
                $cours->setMatiere($matiere);
            }

            $cours->setAuthor($this->getUser());
            $cours->setStatus('APPROVED'); // Auto-approved
            $cours->setCreatedAt(new \DateTimeImmutable());

            $em->persist($cours);
            $em->flush();

            return $this->redirectToRoute('app_admin_courses');
        }

        // Points to templates/admin/new_course.html.twig
        return $this->render('admin/new_course.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 3. MANAGE COURSE (Resources)
    #[Route('/{id}/manage', name: 'app_admin_course_manage', methods: ['GET'])]
    public function manage(Cours $cours): Response
    {
        // Points to templates/admin/manage_course.html.twig
        return $this->render('admin/manage_course.html.twig', [
            'cours' => $cours,
        ]);
    }

    // 4. EDIT COURSE
    #[Route('/{id}/edit', name: 'app_admin_course_edit', methods: ['GET', 'POST'])]
    public function edit(Cours $cours, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CoursType::class, $cours, [
            'hide_matiere' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Course updated successfully!');
            return $this->redirectToRoute('app_admin_category_manage', ['id' => $cours->getMatiere()->getId()]);
        }

        return $this->render('admin/edit_course.html.twig', [
            'cours' => $cours,
            'form' => $form->createView(),
        ]);
    }

    // 5. DELETE COURSE
    #[Route('/{id}/delete', name: 'app_admin_course_delete', methods: ['POST'])]
    public function delete(Cours $cours, Request $request, EntityManagerInterface $em): Response
    {
        $matiereId = $cours->getMatiere()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$cours->getId(), $request->request->get('_token'))) {
            $em->remove($cours);
            $em->flush();

            $this->addFlash('success', 'Course deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_category_manage', ['id' => $matiereId]);
    }
}