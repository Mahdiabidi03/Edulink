<?php

namespace App\Controller;

use App\Service\GeminiService;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AiToolController extends AbstractController
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
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
            $text = $pdf->getText();

            if (empty(trim($text))) {
                return new JsonResponse(['error' => 'Could not extract text from PDF.'], 400);
            }

            // Call AI based on type
            $result = '';
            switch ($type) {
                case 'quiz':
                    $result = $this->geminiService->generateQuiz($text);
                    // Attempt to parse JSON strictly if needed, but for now generic return
                    // Clean up markdown if Gemini adds ```json ... ```
                    $result = preg_replace('/^```json\s*|\s*```$/', '', $result);
                    break;
                case 'summary':
                    $result = $this->geminiService->generateSummary($text);
                    break;
                case 'video_script':
                     $prompt = "Create a 2-minute video script explaining the key concepts of this text. Include scene descriptions and narration. Text: \n\n" . substr($text, 0, 30000);
                     $result = $this->geminiService->generateContent($prompt);
                    break;
                default:
                    return new JsonResponse(['error' => 'Invalid type'], 400);
            }
            return new JsonResponse(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
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
            $result = $this->geminiService->generateContent($prompt);
            
            return new JsonResponse(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
