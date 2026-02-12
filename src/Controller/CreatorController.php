<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Resource;
use App\Form\CoursType;
use App\Form\ResourceType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // <--- This is crucial
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/creator')]
class CreatorController extends AbstractController
{
    // 1. DASHBOARD REDIRECT
    #[Route('/', name: 'app_creator_dashboard')]
    public function index(): Response
    {
        // We redirect to the student dashboard because that's where the tabs are
        return $this->redirectToRoute('app_student_courses');
    }

    // 2. CREATE COURSE
    #[Route('/course/new', name: 'app_creator_course_new')]
    public function newCourse(Request $request, EntityManagerInterface $em): Response
    {
        $cours = new Cours();
        // Check for pre-selected category
        $categoryId = $request->query->get('category');
        $hideMatiere = false;
        
        if ($categoryId) {
            $matiere = $em->getRepository(\App\Entity\Matiere::class)->find($categoryId);
            if ($matiere) {
                $cours->setMatiere($matiere);
                $hideMatiere = true;
            }
        }

        $form = $this->createForm(CoursType::class, $cours, [
            'hide_matiere' => $hideMatiere
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cours->setAuthor($this->getUser());
            $cours->setStatus('PENDING');
            $cours->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($cours);
            $em->flush();
            
            $this->addFlash('success', 'Course proposed successfully! Now you can add resources.');
            return $this->redirectToRoute('app_creator_course_manage', ['id' => $cours->getId()]);
        }

        return $this->render('creator/new_course.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 3. MANAGE COURSE
    #[Route('/course/{id}/manage', name: 'app_creator_course_manage')]
    public function manageCourse(Cours $cours): Response
    {
        // Security check
        if ($cours->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('creator/manage_course.html.twig', [
            'cours' => $cours,
        ]);
    }

    // 4. ADD RESOURCE
    #[Route('/course/{id}/resource/new', name: 'app_creator_resource_new')]
    public function newResource(Cours $cours, Request $request, EntityManagerInterface $em): Response
    {
        // Security check
        if ($cours->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $resource = new Resource();
        $resource->setCours($cours);

        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/resources',
                        $newFilename
                    );
                    $resource->setUrl('/uploads/resources/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed');
                }
            }

            $resource->setAuthor($this->getUser());
            $resource->setStatus('PENDING'); // Student proposals are pending
            
            $em->persist($resource);
            $em->flush();
            
            $this->addFlash('success', 'Resource added successfully! Waiting for moderation.');
            
            return $this->redirectToRoute('app_creator_course_manage', ['id' => $cours->getId()]);
        }

        return $this->render('creator/resource_new.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours
        ]);
    }

    // 6. PROPOSE MATIERE
    #[Route('/matiere/new', name: 'app_creator_matiere_new')]
    public function newMatiere(Request $request, EntityManagerInterface $em): Response
    {
        $matiere = new \App\Entity\Matiere();
        // Students propose categories, so they are PENDING by default
        $matiere->setStatus('PENDING');

        // We can reuse MatiereType since it just has the name field
        $form = $this->createForm(\App\Form\MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/categories',
                        $newFilename
                    );
                    $matiere->setImageUrl('/uploads/categories/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Category image upload failed');
                }
            }
            $matiere->setCreator($this->getUser());
            
            $em->persist($matiere);
            $em->flush();

            // Redirect back to courses, maybe with a flash message saying "Waiting for approval"
            $this->addFlash('success', 'Category proposed successfully! Waiting for admin approval.');
            return $this->redirectToRoute('app_student_courses');
        }

        return $this->render('creator/new_matiere.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}