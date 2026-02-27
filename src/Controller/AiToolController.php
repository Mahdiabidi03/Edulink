<?php

namespace App\Controller;

use App\Service\GroqService;
use Smalot\PdfParser\Parser;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AiToolController extends AbstractController
{
    private GroqService $aiService;
    private LoggerInterface $logger;

    public function __construct(GroqService $aiService, LoggerInterface $logger)
    {
        $this->aiService = $aiService;
        $this->logger = $logger;
    }

    #[Route('/student/ai-tools', name: 'student_ai_tools')]
    public function index(): Response
    {
        return $this->render('student/ai_tools.html.twig');
    }

    #[Route('/api/ai/process-pdf', name: 'api_ai_process_pdf', methods: ['POST'])]
    public function processPdf(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('pdf_file');
        $type = $request->request->get('type'); // 'quiz', 'summary', 'video_script'

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        try {
            // Parse PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());

            // Log parsing attempt
            $this->logger->info('Attempting to extract text from PDF: ' . $file->getClientOriginalName());

            $text = $pdf->getText();

            if (empty(trim($text))) {
                return new JsonResponse(['error' => 'This PDF appears to be an image/scan. Please upload a text-based PDF.'], 400);
            }

            // Call AI based on type
            $result = '';
            switch ($type) {
                case 'quiz':
                    $result = $this->aiService->generateQuiz($text);
                    // Clean up: remove markdown code fences, trim whitespace
                    $result = preg_replace('/^```(?:json)?\s*/m', '', $result);
                    $result = preg_replace('/\s*```\s*$/m', '', $result);
                    $result = trim($result);
                    // If there's text before the JSON array, extract just the array
                    if (preg_match('/(\[[\s\S]*\])/', $result, $matches)) {
                        $result = $matches[1];
                    }
                    break;
                case 'summary':
                    $result = $this->aiService->generateSummary($text);
                    break;
                case 'video_script':
                    $prompt = "Create a professional 2-minute video script explaining the key concepts of this text. 
                     Include clear scene descriptions (e.g., Scene 1: Visual description) and narration/dialogue. 
                     Format it beautifully with Markdown.
                     Text: \n\n" . substr($text, 0, 20000);
                    $result = $this->aiService->generateContent($prompt);
                    break;
                default:
                    return new JsonResponse(['error' => 'Invalid type'], 400);
            }
            return new JsonResponse(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            // Log the actual error to var/log/dev.log
            $this->logger->error('PDF Processing Error: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'Could not extract text from PDF.';

            // Return specific exception message only in dev mode
            if ($this->getParameter('kernel.environment') === 'dev') {
                $message .= ' Error: ' . $e->getMessage();
            }

            return new JsonResponse(['error' => $message], 500);
        }
    }

    #[Route('/api/ai/summarize-course', name: 'api_ai_summarize_course', methods: ['POST'])]
    public function summarizeCourse(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';

        if (!$title && !$description) {
            return new JsonResponse(['error' => 'Missing data'], 400);
        }

        try {
            $prompt = "You are an expert educational advisor. Provide a very short, catchy, and exciting 2-sentence summary of this course for a student. Use emojis. \nCourse Title: $title \nDescription: $description";
            $result = $this->aiService->generateContent($prompt);

            return new JsonResponse(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            $this->logger->error('Course Summarization Error: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
