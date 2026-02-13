<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Note;
use App\Entity\Task;
use App\Entity\Reminder;
use App\Form\TransferPointsType;
use App\Form\EditProfileFormType;
use App\Form\NoteType;
use App\Form\TaskType;
use App\Form\ReminderType;
use App\Repository\NoteRepository;
use App\Repository\TaskRepository;
use App\Repository\ReminderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MatiereRepository;
use App\Repository\CoursRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\ResourceRepository;
use App\Entity\Matiere;
use App\Entity\Cours;
use App\Entity\Enrollment;
use App\Entity\Resource;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    #[Route('/student/notebook', name: 'student_notebook')]
    public function notebook(): Response
    {
        return $this->redirectToRoute('student_journal');
    }

    #[Route('/student/reminders-check', name: 'student_reminders_check', methods: ['GET'])]
    public function checkReminders(
        EntityManagerInterface $entityManager,
        ReminderRepository $reminderRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['reminders' => [], 'debug' => 'No user']);
        }

        $now = new \DateTime();
        $dueReminders = $reminderRepository->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.status = :status')
            ->andWhere('r.reminderTime <= :now')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->setParameter('now', $now)
            ->orderBy('r.reminderTime', 'DESC')
            ->getQuery()
            ->getResult();

        $reminders = [];
        foreach ($dueReminders as $reminder) {
            $reminders[] = [
                'id' => $reminder->getId(),
                'title' => $reminder->getTitle(),
                'description' => $reminder->getDescription(),
                'time' => $reminder->getReminderTime()->format('H:i'),
                'reminderTime' => $reminder->getReminderTime()->format('Y-m-d H:i:s'),
            ];
            $reminder->setStatus('notified');
        }

        if (!empty($dueReminders)) {
            $entityManager->flush();
        }

        return new JsonResponse([
            'reminders' => $reminders,
            'now' => $now->format('Y-m-d H:i:s'),
            'count' => count($reminders)
        ]);
    }

    #[Route('/student/journal', name: 'student_journal')]
    public function journal(
        Request $request,
        EntityManagerInterface $entityManager,
        NoteRepository $noteRepository,
        TaskRepository $taskRepository,
        ReminderRepository $reminderRepository,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Create forms
        $selectedCategoryId = $request->query->get('category');
        $noteSeed = new Note();
        if ($selectedCategoryId) {
            $cat = $entityManager->getRepository(\App\Entity\Category::class)->find($selectedCategoryId);
            if ($cat && $cat->getOwner() === $user) {
                $noteSeed->setCategory($cat);
            }
        }
        $noteForm = $this->createForm(NoteType::class, $noteSeed, ['user' => $user]);
        $taskForm = $this->createForm(TaskType::class, new Task());
        $reminderForm = $this->createForm(ReminderType::class, new Reminder());

        // Handle Note Form
        $noteForm->handleRequest($request);
        if ($noteForm->isSubmitted() && $noteForm->isValid()) {
            $note = $noteForm->getData();
            $note->setUser($user);

            /** @var UploadedFile $attachmentFile */
            $attachmentFile = $noteForm->get('attachment')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$attachmentFile->getClientOriginalExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/notes',
                        $newFilename
                    );
                    $note->setAttachment($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload attachment.');
                }
            }

            $entityManager->persist($note);
            $entityManager->flush();
            $this->addFlash('success', 'Note created successfully!');
            return $this->redirectToRoute('student_journal');
        }

        // Handle Task Form
        $taskForm->handleRequest($request);
        if ($taskForm->isSubmitted() && $taskForm->isValid()) {
            $task = $taskForm->getData();
            $task->setUser($user);
            $entityManager->persist($task);
            $entityManager->flush();
            $this->addFlash('success', 'Task created successfully!');
            return $this->redirectToRoute('student_journal');
        }

        // Handle Reminder Form
        $reminderForm->handleRequest($request);
        if ($reminderForm->isSubmitted() && $reminderForm->isValid()) {
            $reminder = $reminderForm->getData();
            $reminder->setUser($user);
            $entityManager->persist($reminder);
            $entityManager->flush();
            $this->addFlash('success', 'Reminder created successfully!');
            return $this->redirectToRoute('student_journal');
        }

        // Get filters from request
        $query = $request->query->get('q');
        $categoryId = $request->query->get('category');

        $notes = $noteRepository->findByUserWithFilters($user, $query, $categoryId ? (int)$categoryId : null);
        $tasks = $taskRepository->findByUserOrderedByDate($user);
        $reminders = $reminderRepository->findByUserOrderedByDate($user);
        $userCategories = $entityManager->getRepository(\App\Entity\Category::class)->findAllOrderedByName($user);

        return $this->render('student/journal.html.twig', [
            'noteForm' => $noteForm->createView(),
            'taskForm' => $taskForm->createView(),
            'reminderForm' => $reminderForm->createView(),
            'notes' => $notes,
            'tasks' => $tasks,
            'reminders' => $reminders,
            'userCategories' => $userCategories,
            'currentCategory' => $categoryId,
            'searchQuery' => $query,
        ]);
    }

    #[Route('/student/help', name: 'student_help')]
    public function help(): Response
    {
        return $this->redirectToRoute('app_help_request_index');
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
            if ($user->getWalletBalance() < $amount) {
                $this->addFlash('error', 'Insufficient funds!');
                // We keep modal open by passing a flag or using js, 
                // but for simplicity we rely on flash message on reload.
            } elseif ($recipientEmail === $user->getEmail()) {
                $this->addFlash('error', 'You cannot transfer points to yourself.');
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
                    $user->setWalletBalance($user->getWalletBalance() - $amount);
                    $recipient->setWalletBalance(($recipient->getWalletBalance() ?? 0) + $amount);
                    
                    $entityManager->persist($tSender);
                    $entityManager->persist($tRecipient);
                    $entityManager->flush(); // User updates are cascaded or auto-tracked
                    
                    $this->addFlash('success', "Successfully sent $amount PTS to $recipientEmail!");
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

    #[Route('/student/help-history', name: 'student_help_history')]
    public function helpHistory(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $sessions = $user->getSessionsAsTutor()->toArray();
        usort($sessions, fn($a, $b) => $b->getStartedAt() <=> $a->getStartedAt());

        $stats = [
            'totalAssists' => 0,
            'resolvedAssists' => 0,
            'cancelledAssists' => 0,
            'reportedAssists' => 0,
            'totalEarned' => 0,
        ];

        $items = [];
        foreach ($sessions as $s) {
            $req = $s->getHelpRequest();
            $status = $req->getStatus();
            $reason = $req->getCloseReason();
            $bounty = (int) ($req->getBounty() ?? 0);

            $isResolved = ($status === 'CLOSED' && $reason === 'RESOLVED');
            $isCancelled = ($status === 'CLOSED' && $reason === 'CANCELLED');
            $isReported = ($status === 'CLOSED' && $reason === 'REPORTED');

            $stats['totalAssists']++;
            if ($isResolved) {
                $stats['resolvedAssists']++;
                $stats['totalEarned'] += $bounty;
            } elseif ($isCancelled) {
                $stats['cancelledAssists']++;
            } elseif ($isReported) {
                $stats['reportedAssists']++;
            }

            $items[] = [
                'id' => $s->getId(),
                'title' => $req->getTitle(),
                'student' => $req->getStudent()?->getFullName(),
                'startedAt' => $s->getStartedAt(),
                'endedAt' => $s->getEndedAt(),
                'status' => $status,
                'reason' => $reason,
                'bounty' => $bounty,
                'earned' => $isResolved ? $bounty : 0,
            ];
        }

        return $this->render('student/help_history.html.twig', [
            'items' => $items,
            'stats' => $stats,
            'currentBalance' => $user->getWalletBalance(),
        ]);
    }

    // ===== NOTE CRUD ROUTES =====
    #[Route('/student/note/{id}/edit', name: 'student_note_edit', methods: ['GET', 'POST'])]
    public function editNote(
        Note $note,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($note->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot edit this note');
        }

        $form = $this->createForm(NoteType::class, $note, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', 'Note updated successfully!');
            return $this->redirectToRoute('student_journal');
        }

        return $this->render('student/journal_edit_note.html.twig', [
            'form' => $form->createView(),
            'note' => $note,
        ]);
    }

    #[Route('/student/note/{id}/delete', name: 'student_note_delete', methods: ['POST'])]
    public function deleteNote(
        Note $note,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($note->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot delete this note');
        }

        $entityManager->remove($note);
        $entityManager->flush();
        $this->addFlash('success', 'Note deleted successfully!');
        return $this->redirectToRoute('student_journal');
    }

    #[Route('/student/note/{id}/export-pdf', name: 'student_note_export_pdf', methods: ['GET'])]
    public function exportNotePdf(Note $note): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($note->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot export this note');
        }

        // Configure Dompdf options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // Allow loading images from remote/local URLs

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('student/note_pdf.html.twig', [
            'note' => $note,
            'title' => "Note Export"
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'landscape'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="note-'.$note->getId().'.pdf"',
        ]);
    }

    // ===== TASK CRUD ROUTES =====
    #[Route('/student/task/{id}/toggle', name: 'student_task_toggle', methods: ['POST'])]
    public function toggleTask(
        Request $request,
        Task $task,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($task->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot toggle this task');
        }

        $task->setIsCompleted(!$task->isCompleted());
        if ($task->isCompleted()) {
            $task->setCompletedAt(new \DateTime());
        } else {
            $task->setCompletedAt(null);
        }
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true, 'completed' => $task->isCompleted()]);
        }

        // Non-AJAX form submission - redirect back to journal so the user sees updated state
        return $this->redirectToRoute('student_journal');
    }

    #[Route('/student/task/{id}/delete', name: 'student_task_delete', methods: ['POST'])]
    public function deleteTask(
        Task $task,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($task->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot delete this task');
        }

        $entityManager->remove($task);
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    // ===== REMINDER CRUD ROUTES =====
    #[Route('/student/reminder/{id}/edit', name: 'student_reminder_edit')]
    public function editReminder(
        Reminder $reminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($reminder->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot edit this reminder');
        }

        $form = $this->createForm(ReminderType::class, $reminder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Reminder updated successfully!');
            return $this->redirectToRoute('student_journal');
        }

        return $this->render('student/reminder_edit.html.twig', [
            'form' => $form->createView(),
            'reminder' => $reminder,
        ]);
    }

    #[Route('/student/reminder/{id}/delete', name: 'student_reminder_delete', methods: ['POST'])]
    public function deleteReminder(
        Reminder $reminder,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($reminder->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot delete this reminder');
        }

        $entityManager->remove($reminder);
        $entityManager->flush();
        $this->addFlash('success', 'Reminder deleted successfully!');
        return $this->redirectToRoute('student_journal');
    }

    #[Route('/student/reminder/{id}/dismiss', name: 'student_reminder_dismiss', methods: ['POST'])]
    public function dismissReminder(
        Reminder $reminder,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($reminder->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot dismiss this reminder');
        }

        $reminder->setStatus('dismissed');
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route('/student/reminder/{id}/defer', name: 'student_reminder_defer', methods: ['POST'])]
    public function deferReminder(
        Reminder $reminder,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($reminder->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot defer this reminder');
        }

        // Get the defer minutes from request (default 30 minutes)
        $deferMinutes = (int) $request->request->get('defer_minutes', 30);
        
        // Update reminder time by adding defer minutes
        $originalTime = $reminder->getReminderTime();
        $newTime = \DateTime::createFromInterface($originalTime)
            ->modify("+{$deferMinutes} minutes");
        
        $reminder->setReminderTime($newTime);
        
        // Set status back to pending if it was dismissed
        if ($reminder->getStatus() === 'dismissed') {
            $reminder->setStatus('pending');
        }
        
        $entityManager->flush();
        $this->addFlash('success', "Reminder moved to {$newTime->format('H:i')}");
        return $this->redirectToRoute('student_journal');
    }

    #[Route('/student/reminder/{id}/quick-update', name: 'student_reminder_quick_update', methods: ['POST'])]
    public function quickUpdateReminder(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ReminderRepository $reminderRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $reminder = $reminderRepository->find($id);
        
        if (!$reminder) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Reminder not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Check if the reminder belongs to the current user
        if ($reminder->getUser() !== $this->getUser()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Update fields from request data
            if (isset($data['title']) && !empty($data['title'])) {
                $reminder->setTitle($data['title']);
            }
            
            if (isset($data['description'])) {
                $reminder->setDescription($data['description']);
            }
            
            if (isset($data['reminderTime']) && !empty($data['reminderTime'])) {
                // Try to parse the datetime - JavaScript sends YYYY-MM-DDTHH:mm format
                try {
                    $reminderTime = new \DateTime($data['reminderTime']);
                    $reminder->setReminderTime($reminderTime);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Invalid date format: ' . $e->getMessage()
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
            
            // Validate the reminder entity before flushing
            $violations = $validator->validate($reminder);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = [
                        'field' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage()
                    ];
                }
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Reminder updated successfully',
                'reminder' => [
                    'id' => $reminder->getId(),
                    'title' => $reminder->getTitle(),
                    'description' => $reminder->getDescription(),
                    'reminderTime' => $reminder->getReminderTime()->format('Y-m-d H:i'),
                ]
            ]);
        } catch (\ValueError $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid date/time format. Please use the format: YYYY-MM-DD HH:MM',
                'errors' => [
                    [
                        'field' => 'reminderTime',
                        'message' => 'Invalid date/time format'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error updating reminder: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

