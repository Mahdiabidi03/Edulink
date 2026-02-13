<?php

namespace App\Controller;

use App\Entity\UserChallenge;
use App\Repository\ChallengeRepository;
use App\Repository\UserChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChallengeController extends AbstractController
{
    #[Route('/challenges', name: 'challenge_index', methods: ['GET'])]
    public function index(Request $request, ChallengeRepository $challengeRepo, UserChallengeRepository $ucRepo): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $order = (string) $request->query->get('order', 'newest');

        $qb = $challengeRepo->createQueryBuilder('c');

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

        $challenges = $qb->getQuery()->getResult();

        $user = $this->getUser();
        $joinedChallengeIds = [];

        if ($user) {
            $participations = $ucRepo->findBy(['user' => $user]);
            $joinedChallengeIds = array_map(
                fn(UserChallenge $uc) => $uc->getChallenge()?->getId(),
                $participations
            );
            $joinedChallengeIds = array_values(array_filter($joinedChallengeIds));
        }

        return $this->render('challenge/index.html.twig', [
            'challenges' => $challenges,
            'joinedChallengeIds' => $joinedChallengeIds,
            'q' => $q,
            'order' => $order,
        ]);
    }

    #[Route('/challenges/{id}/join', name: 'challenge_join', methods: ['POST'])]
    public function join(
        int $id,
        Request $request,
        ChallengeRepository $challengeRepo,
        UserChallengeRepository $ucRepo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('join_challenge_'.$id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action invalide (CSRF).');
            return $this->redirectToRoute('challenge_index');
        }

        $challenge = $challengeRepo->find($id);
        if (!$challenge) {
            throw $this->createNotFoundException('Challenge not found');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $existing = $ucRepo->findOneBy(['user' => $user, 'challenge' => $challenge]);
        if ($existing) {
            $this->addFlash('info', 'Tu as déjà rejoint ce challenge.');
            return $this->redirectToRoute('challenge_index');
        }

        $uc = new UserChallenge();
        $uc->setUser($user);
        $uc->setChallenge($challenge);
        $uc->setStatus(UserChallenge::STATUS_IN_PROGRESS);
        $uc->setProgress('0/3'); // total fixe
        $uc->setProofFileName(null);

        $em->persist($uc);
        $em->flush();

        $this->addFlash('success', 'Challenge rejoint !');
        return $this->redirectToRoute('challenge_index');
    }

    #[Route('/my-challenges', name: 'my_challenges', methods: ['GET'])]
    public function myChallenges(UserChallengeRepository $ucRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $participations = $ucRepo->findBy(
            ['user' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('challenge/my_challenges.html.twig', [
            'participations' => $participations,
        ]);
    }

    #[Route('/my-challenges/{id}/update', name: 'update_progress', methods: ['POST'])]
    public function updateProgress(
        UserChallenge $userChallenge,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // CSRF
        if (!$this->isCsrfTokenValid('update_progress_'.$userChallenge->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action invalide (CSRF).');
            return $this->redirectToRoute('my_challenges');
        }

        // sécurité : l'étudiant ne modifie que ses propres participations
        if ($userChallenge->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Si déjà COMPLETED, on bloque (déjà validé par admin)
        if ($userChallenge->getStatus() === UserChallenge::STATUS_COMPLETED) {
            $this->addFlash('info', 'Challenge déjà validé.');
            return $this->redirectToRoute('my_challenges');
        }

        $progress = (string) $request->request->get('progress', '0/3');

        // Normaliser progress
        $parts = explode('/', $progress);
        $current = (int) ($parts[0] ?? 0);
        $total   = (int) ($parts[1] ?? 3);

        if ($total <= 0) { $total = 3; }
        if ($current < 0) { $current = 0; }
        if ($current > $total) { $current = $total; }

        $normalized = $current.'/'.$total;
        $userChallenge->setProgress($normalized);

        // ✅ Si 3/3 → obligation d’uploader une preuve → statut PENDING
        if ($current >= $total) {
            $uploadedFile = $request->files->get('proof_file');

            if (!$uploadedFile) {
                $this->addFlash('error', 'Pour terminer, tu dois joindre un fichier de preuve.');
                return $this->redirectToRoute('my_challenges');
            }

            // Sécurité simple : limite extensions (tu peux ajuster)
            $allowedExt = ['pdf', 'png', 'jpg', 'jpeg'];
            $ext = strtolower((string) $uploadedFile->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt, true)) {
                $this->addFlash('error', 'Format invalide. Autorisés: PDF, PNG, JPG, JPEG.');
                return $this->redirectToRoute('my_challenges');
            }

            // Nom unique
            $safeName = 'proof_'.$userChallenge->getId().'_'.uniqid('', true).'.'.$ext;

            // Dossier uploads
            $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/proofs';

            try {
                $uploadedFile->move($targetDir, $safeName);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur upload fichier.');
                return $this->redirectToRoute('my_challenges');
            }

            $userChallenge->setProofFileName($safeName);
            $userChallenge->setStatus(UserChallenge::STATUS_PENDING);

            $this->addFlash('success', 'Preuve envoyée. En attente de validation admin.');
        } else {
            // Si pas terminé → IN_PROGRESS, et on supprime la preuve si elle existait
            $userChallenge->setStatus(UserChallenge::STATUS_IN_PROGRESS);
            $userChallenge->setProofFileName(null);

            $this->addFlash('success', 'Progress updated!');
        }

        $em->flush();
        return $this->redirectToRoute('my_challenges');
    }
}
