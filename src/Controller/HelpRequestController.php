<?php

namespace App\Controller;

use App\Entity\HelpRequest;
use App\Entity\Message;
use App\Entity\Review;
use App\Entity\Session;
use App\Form\HelpRequestType;
use App\Form\MessageType;
use App\Form\ReviewType;
use App\Repository\HelpRequestRepository;
use App\Repository\SessionRepository;
use App\Service\AssistanceService;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * FRONT-OFFICE: Help Board Controller
 * Full CRUD + Chat + Review
 */
#[Route('/help-board')]
#[IsGranted('ROLE_USER')]
class HelpRequestController extends AbstractController
{
    #[Route('/', name: 'app_help_request_index', methods: ['GET'])]
    public function index(HelpRequestRepository $helpRequestRepository, Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search'),
            'min_bounty' => $request->query->get('min_bounty'),
            'sort' => $request->query->get('sort', 'newest'),
        ];

        return $this->render('help_request/index.html.twig', [
            'help_requests' => $helpRequestRepository->findOpenRequests($filters),
            'filters' => $filters,
            'my_requests' => $helpRequestRepository->findBy(
                ['student' => $this->getUser()],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/show/{id}', name: 'app_help_request_show', methods: ['GET'])]
    public function show(HelpRequest $helpRequest): Response
    {
        return $this->render('help_request/show.html.twig', [
            'help_request' => $helpRequest,
        ]);
    }

    #[Route('/new', name: 'app_help_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $helpRequest = new HelpRequest();
        $form = $this->createForm(HelpRequestType::class, $helpRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if ($user->getWalletBalance() < $helpRequest->getBounty()) {
                $this->addFlash('error', 'Insufficient wallet balance for this bounty.');
                return $this->redirectToRoute('app_help_request_new');
            }

            $helpRequest->setStudent($user);
            $entityManager->persist($helpRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Help request posted successfully!');
            return $this->redirectToRoute('app_help_request_index');
        }

        return $this->render('help_request/new.html.twig', [
            'help_request' => $helpRequest,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_help_request_edit', methods: ['GET', 'POST'])]
    public function edit(HelpRequest $helpRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($helpRequest->getStudent() !== $this->getUser()) {
            $this->addFlash('error', 'You can only edit your own requests.');
            return $this->redirectToRoute('app_help_request_index');
        }

        if ($helpRequest->getStatus() !== 'OPEN') {
            $this->addFlash('error', 'Cannot edit a request that is no longer open.');
            return $this->redirectToRoute('app_help_request_index');
        }

        $form = $this->createForm(HelpRequestType::class, $helpRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Help request updated successfully!');
            return $this->redirectToRoute('app_help_request_index');
        }

        return $this->render('help_request/edit.html.twig', [
            'help_request' => $helpRequest,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_help_request_delete', methods: ['POST'])]
    public function delete(HelpRequest $helpRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($helpRequest->getStudent() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own requests.');
            return $this->redirectToRoute('app_help_request_index');
        }

        if ($helpRequest->getStatus() !== 'OPEN') {
            $this->addFlash('error', 'Cannot delete a request that is in progress or closed.');
            return $this->redirectToRoute('app_help_request_index');
        }

        $entityManager->remove($helpRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Help request deleted.');
        return $this->redirectToRoute('app_help_request_index');
    }

    #[Route('/join/{id}', name: 'app_help_request_join', methods: ['POST'])]
    public function join(HelpRequest $helpRequest, AssistanceService $assistanceService): Response
    {
        $user = $this->getUser();

        if ($helpRequest->getStudent() === $user) {
            $this->addFlash('error', 'You cannot join your own request.');
            return $this->redirectToRoute('app_help_request_index');
        }

        if ($helpRequest->getStatus() !== 'OPEN') {
            $this->addFlash('error', 'This request is no longer open.');
            return $this->redirectToRoute('app_help_request_index');
        }

        $session = new Session();
        $session->setHelpRequest($helpRequest);
        $session->setTutor($user);

        $assistanceService->createSession($session);

        return $this->redirectToRoute('app_help_request_chat', ['id' => $session->getId()]);
    }

    #[Route('/chat/{id}', name: 'app_help_request_chat', methods: ['GET', 'POST'])]
    public function chat(
        Session $session,
        Request $request,
        EntityManagerInterface $entityManager,
        GeminiService $geminiService
    ): Response
    {
        $user = $this->getUser();

        if ($session->getTutor() !== $user && $session->getHelpRequest()->getStudent() !== $user) {
            throw $this->createAccessDeniedException('You are not a participant in this session.');
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$session->isIsActive()) {
                $this->addFlash('error', 'This session is closed.');
                return $this->redirectToRoute('app_help_request_chat', ['id' => $session->getId()]);
            }

            $content = $message->getContent();

            if ($geminiService->isToxic($content)) {
                $message->setIsToxic(true);
                $this->addFlash('warning', 'Your message triggered our toxicity filter but was sent.');
            }

            $message->setSession($session);
            $message->setSender($user);

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_help_request_chat', ['id' => $session->getId()]);
        }

        return $this->render('help_request/chat.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/message/edit/{id}', name: 'app_help_request_message_edit', methods: ['GET', 'POST'])]
    public function editMessage(Message $message, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($message->getSender() !== $this->getUser()) {
            $this->addFlash('error', 'You can only edit your own messages.');
            return $this->redirectToRoute('app_help_request_chat', ['id' => $message->getSession()->getId()]);
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Message updated.');
            return $this->redirectToRoute('app_help_request_chat', ['id' => $message->getSession()->getId()]);
        }

        return $this->render('help_request/edit_message.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/close/{id}', name: 'app_help_request_close', methods: ['POST'])]
    public function close(Session $session, Request $request, AssistanceService $assistanceService): Response
    {
        $user = $this->getUser();
        $helpRequest = $session->getHelpRequest();

        if ($helpRequest->getStudent() !== $user && $session->getTutor() !== $user) {
            $this->addFlash('error', 'Only session participants can close.');
            return $this->redirectToRoute('app_help_request_chat', ['id' => $session->getId()]);
        }

        $reason = $request->request->get('close_reason', 'RESOLVED');

        if (!in_array($reason, ['RESOLVED', 'CANCELLED', 'REPORTED'])) {
            $reason = 'RESOLVED';
        }

        $helpRequest->setCloseReason($reason);

        if ($reason === 'REPORTED') {
            $helpRequest->setIsTicket(true);
        }

        if ($reason === 'RESOLVED') {
            $assistanceService->closeSession($session);
            $this->addFlash('success', 'Session closed. Bounty transferred. Please rate your tutor.');
            return $this->redirectToRoute('app_help_request_review', ['id' => $session->getId()]);
        }

        $session->setIsActive(false);
        $session->setEndedAt(new \DateTimeImmutable());
        $helpRequest->setStatus('CLOSED');
        $assistanceService->getEntityManager()->flush();

        if ($reason === 'REPORTED') {
            $this->addFlash('warning', 'Session reported. An admin will review this.');
        } else {
            $this->addFlash('info', 'Session cancelled.');
        }

        return $this->redirectToRoute('app_help_request_index');
    }

    #[Route('/review/{id}', name: 'app_help_request_review', methods: ['GET', 'POST'])]
    public function review(Session $session, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($session->getHelpRequest()->getStudent() !== $this->getUser()) {
            return $this->redirectToRoute('app_help_request_index');
        }

        if ($session->getReview()) {
            $this->addFlash('info', 'You have already reviewed this session.');
            return $this->redirectToRoute('app_help_request_index');
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setSession($session);
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Thank you for your feedback!');
            return $this->redirectToRoute('app_help_request_index');
        }

        return $this->render('help_request/review.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    // ==================== COMMUNITY FEED ====================

    #[Route('/community', name: 'app_community_feed', methods: ['GET', 'POST'])]
    public function communityFeed(
        Request $request,
        EntityManagerInterface $entityManager,
        \App\Repository\CommunityPostRepository $postRepo,
        \App\Repository\PostCommentRepository $commentRepo,
        \App\Repository\PostReactionRepository $reactionRepo
    ): Response {
        $post = new \App\Entity\CommunityPost();
        $form = $this->createForm(\App\Form\CommunityPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post published!');
            return $this->redirectToRoute('app_community_feed');
        }

        $communityPosts = $postRepo->findRecentPosts(30);
        $user = $this->getUser();

        // Build feed items with comments and reactions
        $feedItems = [];
        foreach ($communityPosts as $p) {
            $reactions = $reactionRepo->getReactionCounts($p->getId());
            $userReaction = $reactionRepo->findUserReaction($user->getId(), $p->getId());
            $comments = $commentRepo->findByPost($p->getId());

            $feedItems[] = [
                'post' => $p,
                'reactions' => $reactions,
                'userReaction' => $userReaction ? $userReaction->getType() : null,
                'totalReactions' => array_sum($reactions),
                'comments' => $comments,
                'commentCount' => count($comments),
            ];
        }

        // Filter
        $feedFilter = $request->query->get('feed_filter', 'all');
        if ($feedFilter !== 'all') {
            $feedItems = array_filter($feedItems, fn($item) =>
                $item['post']->getType() === $feedFilter
            );
        }

        return $this->render('help_request/community_feed.html.twig', [
            'form' => $form,
            'feed_items' => $feedItems,
            'feed_filter' => $feedFilter,
            'post_count' => count($communityPosts),
        ]);
    }

    #[Route('/community/react/{id}/{type}', name: 'app_community_react', methods: ['POST'])]
    public function reactToPost(
        \App\Entity\CommunityPost $post,
        string $type,
        EntityManagerInterface $entityManager,
        \App\Repository\PostReactionRepository $reactionRepo
    ): Response {
        $allowed = ['like', 'love', 'insightful', 'funny', 'support'];
        if (!in_array($type, $allowed)) {
            $this->addFlash('error', 'Invalid reaction type.');
            return $this->redirectToRoute('app_community_feed');
        }

        $user = $this->getUser();
        $existing = $reactionRepo->findUserReaction($user->getId(), $post->getId());

        if ($existing) {
            if ($existing->getType() === $type) {
                // Toggle off
                $entityManager->remove($existing);
            } else {
                // Change reaction
                $existing->setType($type);
            }
        } else {
            $reaction = new \App\Entity\PostReaction();
            $reaction->setUser($user);
            $reaction->setPost($post);
            $reaction->setType($type);
            $entityManager->persist($reaction);
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_community_feed');
    }

    #[Route('/community/comment/{id}', name: 'app_community_comment', methods: ['POST'])]
    public function addComment(
        \App\Entity\CommunityPost $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $content = trim($request->request->get('comment_content', ''));
        if (empty($content)) {
            $this->addFlash('error', 'Comment cannot be empty.');
            return $this->redirectToRoute('app_community_feed');
        }

        if (strlen($content) > 1000) {
            $this->addFlash('error', 'Comment too long (max 1000 chars).');
            return $this->redirectToRoute('app_community_feed');
        }

        $comment = new \App\Entity\PostComment();
        $comment->setAuthor($this->getUser());
        $comment->setPost($post);
        $comment->setContent($content);
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment posted!');
        return $this->redirectToRoute('app_community_feed');
    }

    #[Route('/community/comment/delete/{id}', name: 'app_community_comment_delete', methods: ['POST'])]
    public function deleteComment(
        \App\Entity\PostComment $comment,
        EntityManagerInterface $entityManager
    ): Response {
        if ($comment->getAuthor() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own comments.');
            return $this->redirectToRoute('app_community_feed');
        }
        $entityManager->remove($comment);
        $entityManager->flush();
        $this->addFlash('success', 'Comment deleted.');
        return $this->redirectToRoute('app_community_feed');
    }

    #[Route('/community/report/{id}', name: 'app_community_report', methods: ['POST'])]
    public function reportPost(
        \App\Entity\CommunityPost $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $reason = $request->request->get('report_reason', 'inappropriate');
        $details = trim($request->request->get('report_details', ''));

        $allowed = ['inappropriate', 'spam', 'harassment', 'misinformation', 'hate_speech', 'other'];
        if (!in_array($reason, $allowed)) { $reason = 'inappropriate'; }

        $report = new \App\Entity\PostReport();
        $report->setReporter($this->getUser());
        $report->setPost($post);
        $report->setReason($reason);
        if ($details) { $report->setDetails($details); }
        $entityManager->persist($report);
        $entityManager->flush();

        $this->addFlash('success', 'Report submitted to admins. Thank you for keeping the community safe.');
        return $this->redirectToRoute('app_community_feed');
    }

    #[Route('/community/delete/{id}', name: 'app_community_delete', methods: ['POST'])]
    public function deleteCommunityPost(
        \App\Entity\CommunityPost $post,
        EntityManagerInterface $entityManager
    ): Response {
        if ($post->getAuthor() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own posts.');
            return $this->redirectToRoute('app_community_feed');
        }

        // Delete related comments, reactions, reports
        $entityManager->createQuery('DELETE FROM App\Entity\PostComment c WHERE c.post = :post')
            ->setParameter('post', $post)->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\PostReaction r WHERE r.post = :post')
            ->setParameter('post', $post)->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\PostReport r WHERE r.post = :post')
            ->setParameter('post', $post)->execute();

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post deleted.');
        return $this->redirectToRoute('app_community_feed');
    }
}
