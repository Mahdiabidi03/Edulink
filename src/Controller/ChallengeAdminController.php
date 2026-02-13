<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\UserChallenge;
use App\Form\ChallengeType;
use App\Repository\ChallengeRepository;
use App\Repository\UserChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/challenge/admin')]
final class ChallengeAdminController extends AbstractController
{
    /* ========================
       CHALLENGE CRUD
    ======================== */

    #[Route(name: 'app_challenge_admin_index', methods: ['GET'])]
    public function index(Request $request, ChallengeRepository $challengeRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $order = (string) $request->query->get('order', 'newest');

        $qb = $challengeRepository->createQueryBuilder('c');

        if ($q !== '') {
            $qb->andWhere('c.title LIKE :q OR c.goal LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        switch ($order) {
            case 'alpha_asc':
                $qb->orderBy('c.title', 'ASC');
                break;
            case 'alpha_desc':
                $qb->orderBy('c.title', 'DESC');
                break;
            case 'xp_desc':
                $qb->orderBy('c.rewardPoints', 'DESC');
                break;
            case 'xp_asc':
                $qb->orderBy('c.rewardPoints', 'ASC');
                break;
            case 'newest':
            default:
                $qb->orderBy('c.id', 'DESC');
                break;
        }

        return $this->render('challenge_admin/index.html.twig', [
            'challenges' => $qb->getQuery()->getResult(),
            'q' => $q,
            'order' => $order,
        ]);
    }

    #[Route('/new', name: 'app_challenge_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $challenge = new Challenge();
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($challenge);
            $entityManager->flush();
            return $this->redirectToRoute('app_challenge_admin_index');
        }

        return $this->render('challenge_admin/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_challenge_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Challenge $challenge, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_challenge_admin_index');
        }

        return $this->render('challenge_admin/edit.html.twig', [
            'challenge' => $challenge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_challenge_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Challenge $challenge, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$challenge->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($challenge);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_challenge_admin_index');
    }

    /* ========================
       VALIDATION SECTION
    ======================== */

    #[Route('/submissions', name: 'admin_submissions', methods: ['GET'])]
    public function submissions(UserChallengeRepository $ucRepo): Response
    {
        $submissions = $ucRepo->findBy(
            ['status' => UserChallenge::STATUS_PENDING],
            ['id' => 'DESC']
        );

        return $this->render('challenge_admin/submissions.html.twig', [
            'submissions' => $submissions
        ]);
    }

    #[Route('/submission/{id}/validate', name: 'admin_validate_submission', methods: ['POST'])]
    public function validateSubmission(
        UserChallenge $userChallenge,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        if (!$this->isCsrfTokenValid('validate_'.$userChallenge->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_submissions');
        }

        if ($userChallenge->getStatus() !== UserChallenge::STATUS_PENDING) {
            return $this->redirectToRoute('admin_submissions');
        }

        // Donner les points
        $user = $userChallenge->getUser();
        $reward = $userChallenge->getChallenge()->getRewardPoints() ?? 0;

        // Mise à jour de l'XP (pour affichage et progression)
        $user->setXp(($user->getXp() ?? 0) + $reward);
        
        // Mise à jour du wallet (pour les transactions/paiements si nécessaire)
        $user->setWalletBalance(($user->getWalletBalance() ?? 0) + $reward);

        $userChallenge->setStatus(UserChallenge::STATUS_COMPLETED);

        $em->flush();

        $this->addFlash('success', 'Challenge validé. Points crédités.');
        return $this->redirectToRoute('admin_submissions');
    }

    #[Route('/submission/{id}/reject', name: 'admin_reject_submission', methods: ['POST'])]
    public function rejectSubmission(
        UserChallenge $userChallenge,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        if (!$this->isCsrfTokenValid('reject_'.$userChallenge->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_submissions');
        }

        if ($userChallenge->getStatus() !== UserChallenge::STATUS_PENDING) {
            return $this->redirectToRoute('admin_submissions');
        }

        $userChallenge->setStatus(UserChallenge::STATUS_IN_PROGRESS);
        $userChallenge->setProofFileName(null);

        $em->flush();

        $this->addFlash('info', 'Soumission refusée.');
        return $this->redirectToRoute('admin_submissions');
    }
}
