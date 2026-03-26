<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\Tool;
use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneratePdfController extends AbstractController
{
    private PdfGeneratorService $pdfService;
    private EntityManagerInterface $em;
    private GenerationRepository $generationRepository;
    private ToolRepository $toolRepository;

    public function __construct(
        PdfGeneratorService $pdfService,
        EntityManagerInterface $em,
        GenerationRepository $generationRepository,
        ToolRepository $toolRepository
    ) {
        $this->pdfService = $pdfService;
        $this->em = $em;
        $this->generationRepository = $generationRepository;
        $this->toolRepository = $toolRepository;
    }

    private function checkDailyLimit(): bool
    {
        $user = $this->getUser();
        $plan = $user->getPlan();

        if ($plan->getLimitGeneration() === null) {
            return true;
        }

        $todayStart = new \DateTime('today');
        $todayPdfs = $this->generationRepository->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        return $todayPdfs < $plan->getLimitGeneration();
    }

    public function index(): Response
    {
        $user = $this->getUser();

        // Compte les PDFs générés aujourd'hui
        $todayStart = new \DateTime('today');
        $todayPdfs = $this->generationRepository->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        // Récupère tous les tools actifs
        $allTools = $this->toolRepository->findAllActive();

        return $this->render('pdf/index.html.twig', [
            'today_pdfs' => $todayPdfs,
            'tools' => $allTools,
        ]);
    }

    public function convertTool(string $slug, Request $request): Response
    {
        $tool = $this->toolRepository->findOneBySlug($slug);

        if (!$tool) {
            throw $this->createNotFoundException('Outil introuvable.');
        }

        $this->denyAccessUnlessGranted('TOOL_ACCESS', $tool);

        if ($request->isMethod('POST')) {
            if (!$this->checkDailyLimit()) {
                $this->addFlash('error', 'Vous avez atteint votre limite de génération pour aujourd\'hui.');
                return $this->redirectToRoute('app_pdf_index');
            }

            try {
                $pdfContent = match ($slug) {
                    'url' => $this->generateFromUrl($request),
                    'html' => $this->generateFromFile($request),
                    'wysiwyg' => $this->generateFromWysiwyg($request),
                    'merge' => $this->generateFromMerge($request),
                    'split' => $this->generateFromSplit($request),
                    'screenshot' => $this->generateFromScreenshot($request),
                    'office' => $this->generateFromOffice($request),
                    'text' => $this->generateFromText($request),
                    default => throw new \Exception('Type de génération non supporté')
                };

                $filename = 'pdfgenerator_' . time() . '.pdf';
                $filepath = $this->pdfService->savePdf($pdfContent, $filename);

                $user = $this->getUser();
                $plan = $user->getPlan();


                if ($plan->getLimitStorage() !== null) {

                    $currentCount = $this->generationRepository->count(['user' => $user]);

                    if ($currentCount >= $plan->getLimitStorage()) {
                        $toDelete = $currentCount - $plan->getLimitStorage() + 1;

                        $oldestGenerations = $this->generationRepository->findBy(
                            ['user' => $user],
                            ['createdAt' => 'ASC'],
                            $toDelete
                        );

                        foreach ($oldestGenerations as $oldGen) {
                            $filePath = __DIR__ . '/../../public/' . $oldGen->getFile();
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                            $this->em->remove($oldGen);
                        }
                        $this->em->flush();
                    }
                }

                $generation = new Generation();
                $generation->setUser($user);
                $generation->setFile($filepath);
                $generation->setType($slug);
                $generation->setCustomName('pdfgenerator_' . $slug . '.pdf');
                $generation->setCreatedAt(new \DateTimeImmutable());
                $this->em->persist($generation);
                $this->em->flush();

                return $this->redirectToRoute('app_pdf_result', ['id' => $generation->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        $template = match ($slug) {
            'url' => 'pdf/generate_url.html.twig',
            'html' => 'pdf/generate_file.html.twig',
            'wysiwyg' => 'pdf/generate_wysiwyg.html.twig',
            'merge' => 'pdf/generate_merge.html.twig',
            'split' => 'pdf/generate_split.html.twig',
            'screenshot' => 'pdf/generate_screenshot.html.twig',
            'office' => 'pdf/generate_office.html.twig',
            'text' => 'pdf/generate_text.html.twig',

            default => throw $this->createNotFoundException('Template introuvable')
        };

        return $this->render($template, [
            'tool' => $tool,
        ]);
    }

    private function generateFromUrl(Request $request): string
    {
        $url = $request->request->get('url');
        return $this->pdfService->generatePdfFromUrl($url);
    }

    private function generateFromFile(Request $request): string
    {
        $file = $request->files->get('file');
        if (!$file) {
            throw new \Exception('Aucun fichier uploadé');
        }

        $content = file_get_contents($file->getPathname());
        return $this->pdfService->generateFromHtml($content);
    }

    private function generateFromWysiwyg(Request $request): string
    {
        $title = $request->request->get('title');
        $content = $request->request->get('content');

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
            color: #333;
        }
        h1 {
            color: #3B82F6;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        p {
            text-align: justify;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>
    <p>' . nl2br(htmlspecialchars($content)) . '</p>
</body>
</html>';

        return $this->pdfService->generateFromHtml($html);
    }
    private function generateFromMerge(Request $request): string
    {
        $files = $request->files->get('files');

        if (!$files || count($files) < 2) {
            throw new \Exception('Veuillez uploader au moins 2 fichiers PDF');
        }

        return $this->pdfService->mergePdfs($files);
    }

    private function generateFromSplit(Request $request): string
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw new \Exception('Aucun fichier uploadé');
        }

        if (!$file->isValid()) {
            throw new \Exception('Le fichier uploadé est invalide : ' . $file->getErrorMessage());
        }

        $startPage = (int) $request->request->get('start_page', 1);
        $endPage = (int) $request->request->get('end_page', 1);

        if ($startPage < 1 || $endPage < $startPage) {
            throw new \Exception('Numéros de pages invalides');
        }

        return $this->pdfService->splitPdf($file, $startPage, $endPage);
    }
    private function generateFromScreenshot(Request $request): string
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            throw new \Exception('Aucune image uploadée');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            throw new \Exception('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP');
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move(dirname($tempPath), basename($tempPath));

        $pdfContent = $this->pdfService->generateFromImage($tempPath);

        unlink($tempPath);

        return $pdfContent;
    }
    private function generateFromOffice(Request $request): string
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw new \Exception('Aucun fichier sélectionné.');
        }

        $allowedExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Format non supporté.');
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
        $file->move(dirname($tempPath), basename($tempPath));

        $pdfContent = $this->pdfService->generateFromOffice($tempPath);
        unlink($tempPath);

        if (!$pdfContent) {
            throw new \Exception('Erreur lors de la conversion.');
        }

        return $pdfContent;
    }
    private function generateFromText(Request $request): string
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw new \Exception('Aucun fichier sélectionné.');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'txt') {
            throw new \Exception('Format non supporté. Seul le format TXT est accepté.');
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.txt';
        $file->move(dirname($tempPath), basename($tempPath));

        $pdfContent = $this->pdfService->generateFromText($tempPath);
        unlink($tempPath);

        if (!$pdfContent) {
            throw new \Exception('Erreur lors de la conversion.');
        }

        return $pdfContent;
    }
    public function result(int $id): Response
    {
        $generation = $this->generationRepository->find($id);

        if (!$generation || $generation->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('PDF introuvable');
        }

        $contacts = $this->em->getRepository(\App\Entity\UserContact::class)
            ->findBy(['user' => $this->getUser()]);

        return $this->render('pdf/result.html.twig', [
            'generation' => $generation,
            'contacts' => $contacts,
        ]);
    }
}