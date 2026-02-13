<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Cours;
use App\Form\MatiereType;
use App\Repository\MatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categories')]
class AdminMatiereController extends AbstractController
{
    #[Route('/', name: 'app_admin_categories')]
    public function index(Request $request, MatiereRepository $matiereRepo): Response
    {
        $q = $request->query->get('q');
        $sort = $request->query->get('sort', 'name_asc');

        $qb = $matiereRepo->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', 'APPROVED');

        if ($q) {
            $qb->andWhere('m.name LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        if ($sort === 'newest') {
            $qb->orderBy('m.id', 'DESC');
        } else {
            $qb->orderBy('m.name', 'ASC');
        }

        return $this->render('admin/categories/index.html.twig', [
            'matieres' => $qb->getQuery()->getResult(),
            'q' => $q,
            'sort' => $sort,
        ]);
    }

    #[Route('/{id}/manage', name: 'app_admin_category_manage')]
    public function manage(Matiere $matiere, Request $request, EntityManagerInterface $em): Response
    {
        $q = $request->query->get('q');
        $level = $request->query->get('level');
        $sort = $request->query->get('sort', 'newest');

        $coursRepo = $em->getRepository(Cours::class);
        $qb = $coursRepo->createQueryBuilder('c')
            ->where('c.matiere = :matiere')
            ->setParameter('matiere', $matiere);

        if ($q) {
            $qb->andWhere('c.title LIKE :q OR c.description LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        if ($level) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $level);
        }

        switch ($sort) {
            case 'reward_high':
                $qb->orderBy('c.xp', 'DESC');
                break;
            case 'reward_low':
                $qb->orderBy('c.xp', 'ASC');
                break;
            case 'alpha_asc':
                $qb->orderBy('c.title', 'ASC');
                break;
            default:
                $qb->orderBy('c.id', 'DESC');
        }

        return $this->render('admin/categories/manage.html.twig', [
            'matiere' => $matiere,
            'courses' => $qb->getQuery()->getResult(),
            'q' => $q,
            'level' => $level,
            'sort' => $sort,
        ]);
    }

    #[Route('/{id}/new-course', name: 'app_admin_category_new_course', methods: ['GET', 'POST'])]
    public function newCourse(Matiere $matiere, Request $request, EntityManagerInterface $em): Response
    {
        $cours = new Cours();
        $cours->setMatiere($matiere);
        $cours->setStatus('APPROVED'); // Admin-created = auto-approved
        $cours->setAuthor($this->getUser());
        $cours->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(\App\Form\CoursType::class, $cours, [
            'hide_matiere' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cours);
            $em->flush();

            $this->addFlash('success', 'Course added successfully!');
            return $this->redirectToRoute('app_admin_category_manage', ['id' => $matiere->getId()]);
        }

        return $this->render('admin/categories/new_course.html.twig', [
            'matiere' => $matiere,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function newMatiere(Request $request, EntityManagerInterface $em): Response
    {
        $matiere = new Matiere();
        $matiere->setStatus('APPROVED'); // Admin-created = auto-approved
        $matiere->setCreator($this->getUser());

        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->getClientOriginalExtension();
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
            $em->persist($matiere);
            $em->flush();

            $this->addFlash('success', 'Category created successfully!');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Matiere $matiere, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->getClientOriginalExtension();
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
            $em->flush();

            $this->addFlash('success', 'Category updated successfully!');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'matiere' => $matiere,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Matiere $matiere, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->request->get('_token'))) {
            $em->remove($matiere);
            $em->flush();

            $this->addFlash('success', 'Category deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_categories');
    }
}
