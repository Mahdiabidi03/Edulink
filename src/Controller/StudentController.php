<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransferPointsType;
use App\Form\EditProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StudentController extends AbstractController
{
    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function dashboard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('student_dashboard');
        }

        return $this->render('student/dashboard.html.twig', [
            'editProfileForm' => $form->createView(),
        ]);
    }

    #[Route('/student/journal', name: 'student_journal')]
    public function journal(): Response
    {
        return $this->render('student/journal.html.twig');
    }

    #[Route('/student/help', name: 'student_help')]
    public function help(): Response
    {
        return $this->render('student/help.html.twig');
    }

    #[Route('/student/events', name: 'student_events')]
    public function events(): Response
    {
        return $this->render('student/events.html.twig');
    }

    #[Route('/student/ai-tools', name: 'student_ai_tools')]
    public function aiTools(): Response
    {
        return $this->render('student/ai_tools.html.twig');
    }

    #[Route('/student/wallet', name: 'student_wallet')]
    public function wallet(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // --- Handle Transfer Form ---
        $transferForm = $this->createForm(TransferPointsType::class);
        $transferForm->handleRequest($request);

        if ($transferForm->isSubmitted() && $transferForm->isValid()) {
            $data = $transferForm->getData();
            $amount = $data['amount'];
            $recipientEmail = $data['recipientEmail'];

            // Validation 1: Check Balance
            if ($user->getXp() < $amount) {
                $this->addFlash('error', 'Insufficient XP!');
                // We keep modal open by passing a flag or using js, 
                // but for simplicity we rely on flash message on reload.
            } elseif ($recipientEmail === $user->getEmail()) {
                $this->addFlash('error', 'You cannot transfer XP to yourself.');
            } else {
                // Validation 2: Find Recipient
                $recipient = $entityManager->getRepository(User::class)->findOneBy(['email' => $recipientEmail]);
                
                if (!$recipient) {
                    $this->addFlash('error', 'Recipient not found.');
                } else {
                    // Execute Transfer
                    // 1. Sender Transaction
                    $tSender = new Transaction();
                    $tSender->setUser($user);
                    $tSender->setAmount(-$amount);
                    $tSender->setType('TRANSFER_SENT');
                    $tSender->setDate(new \DateTime());
                    
                    // 2. Recipient Transaction
                    $tRecipient = new Transaction();
                    $tRecipient->setUser($recipient);
                    $tRecipient->setAmount($amount);
                    $tRecipient->setType('TRANSFER_RECEIVED');
                    $tRecipient->setDate(new \DateTime());
                    
                    // 3. Update Balances
                    $user->setXp($user->getXp() - $amount);
                    $recipient->setXp($recipient->getXp() + $amount);
                    
                    $entityManager->persist($tSender);
                    $entityManager->persist($tRecipient);
                    $entityManager->flush(); // User updates are cascaded or auto-tracked
                    
                    $this->addFlash('success', "Successfully sent $amount XP to $recipientEmail!");
                    return $this->redirectToRoute('student_wallet');
                }
            }
        }

        // --- Get Transactions & Stats ---
        $transactions = $user->getTransactions();
        
        $breakdown = [
            'COURSES' => 0,
            'EVENTS' => 0,
            'TRANSFERS' => 0
        ];
        $totalSpent = 0;
        $totalEarned = 0;

        foreach ($transactions as $t) {
            $amount = $t->getAmount();
            $type = strtoupper($t->getType() ?? 'OTHER');

            if ($amount < 0) {
                $absAmount = abs($amount);
                $totalSpent += $absAmount;
                
                if (str_contains($type, 'COURSE')) {
                    $breakdown['COURSES'] += $absAmount;
                } elseif (str_contains($type, 'EVENT')) {
                    $breakdown['EVENTS'] += $absAmount;
                } elseif (str_contains($type, 'TRANSFER')) {
                    $breakdown['TRANSFERS'] += $absAmount;
                }
            } else {
                $totalEarned += $amount;
            }
        }
        
        $transactionsParams = $transactions->toArray();
        usort($transactionsParams, fn($a, $b) => $b->getDate() <=> $a->getDate());

        return $this->render('student/wallet.html.twig', [
            'transactions' => $transactionsParams,
            'breakdown' => $breakdown,
            'totalSpent' => $totalSpent,
            'totalEarned' => $totalEarned,
            'transferForm' => $transferForm->createView(),
        ]);
    }
}