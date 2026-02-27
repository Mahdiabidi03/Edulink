<?php

namespace App\Controller;

use App\Service\AiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/admin/ai')]
class AiController extends AbstractController
{
    private AiService $aiService;
    private LoggerInterface $logger;

    public function __construct(AiService $aiService, LoggerInterface $logger)
    {
        $this->aiService = $aiService;
        $this->logger = $logger;
    }

    #[Route('/generate-missions', name: 'admin_ai_generate_missions', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $this->logger->info("AiController: Request received");
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON input'], 400);
        }

        $title = $data['title'] ?? '';
        $goal = $data['goal'] ?? '';
        $count = (int) ($data['count'] ?? 3);

        if (empty($title)) {
            return new JsonResponse(['error' => 'Le titre est requis'], 400);
        }

        try {
            $missions = $this->aiService->generateMissions($title, $goal, $count);
            return new JsonResponse($missions);
        } catch (\Exception $e) {
            $this->logger->error("AiController Error: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
