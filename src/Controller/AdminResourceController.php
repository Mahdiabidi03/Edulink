<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Chapter;
use App\Entity\Resource;
use App\Form\ResourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/resource')]
class AdminResourceController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_admin_resource_new', methods: ['GET', 'POST'])]
    public function new(Cours $cours, Request $request, EntityManagerInterface $entityManager): Response
    {
        $resource = new Resource();
        $resource->setCours($cours);
        
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid().'.'.$file->getClientOriginalExtension();
                
                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/resources',
                        $newFilename
                    );
                    $resource->setUrl('/uploads/resources/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                }
            }

            // Admin creates = auto-approved
            $resource->setAuthor($this->getUser());
            $resource->setStatus('APPROVED');
            
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'Resource added successfully!');

            return $this->redirectToRoute('app_admin_course_manage', ['id' => $cours->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/resource/new.html.twig', [
            'resource' => $resource,
            'cours' => $cours,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_resource_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Resource $resource, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file) {
                // Remove old file if it exists
                if ($resource->getUrl() && str_starts_with($resource->getUrl(), '/uploads/resources/')) {
                    $oldPath = $this->getParameter('kernel.project_dir') . '/public' . $resource->getUrl();
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $newFilename = uniqid().'.'.$file->getClientOriginalExtension();
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

            $entityManager->flush();
            $this->addFlash('success', 'Resource updated successfully!');

            return $this->redirectToRoute('app_admin_course_manage', ['id' => $resource->getCours()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/resource/edit.html.twig', [
            'resource' => $resource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_resource_delete', methods: ['POST'])]
    public function delete(Request $request, Resource $resource, EntityManagerInterface $entityManager): Response
    {
        $courseId = $resource->getCours()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$resource->getId(), $request->request->get('_token'))) {
            $entityManager->remove($resource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_course_manage', ['id' => $courseId], Response::HTTP_SEE_OTHER);
    }
}
