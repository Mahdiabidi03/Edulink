<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AssistanceService
{
    public function __construct(private
        EntityManagerInterface $entityManager
        )
    {
    }

    public function createSession(Session $session): void
    {
        $this->entityManager->persist($session);
        // Connect session to help request
        $helpRequest = $session->getHelpRequest();
        $helpRequest->setStatus('IN_PROGRESS');

        $this->entityManager->flush();
    }

    public function closeSession(Session $session): void
    {
        if (!$session->isIsActive()) {
            return;
        }

        $this->entityManager->beginTransaction();

        try {
            // 1. Close Session
            $session->setIsActive(false);
            $session->setEndedAt(new \DateTimeImmutable());

            $helpRequest = $session->getHelpRequest();
            $helpRequest->setStatus('CLOSED');

            // 2. Transfer Bounty
            $bounty = $helpRequest->getBounty();
            $student = $helpRequest->getStudent();
            $tutor = $session->getTutor();

            if ($bounty > 0) {
                // Deduct from Student
                $newStudentBalance = $student->getWalletBalance() - $bounty;
                $student->setWalletBalance($newStudentBalance);

                $debitTransaction = new Transaction();
                $debitTransaction->setUser($student);
                $debitTransaction->setAmount(-$bounty);
                $debitTransaction->setType('ASSISTANCE_PAYMENT');
                $debitTransaction->setDate(new \DateTime());
                $this->entityManager->persist($debitTransaction);

                // Credit to Tutor
                $newTutorBalance = $tutor->getWalletBalance() + $bounty;
                $tutor->setWalletBalance($newTutorBalance);

                $creditTransaction = new Transaction();
                $creditTransaction->setUser($tutor);
                $creditTransaction->setAmount($bounty);
                $creditTransaction->setType('ASSISTANCE_REWARD');
                $creditTransaction->setDate(new \DateTime());
                $this->entityManager->persist($creditTransaction);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

        }
        catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
