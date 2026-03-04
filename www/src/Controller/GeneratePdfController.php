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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            // Vérifie la limite quotidienne
            if (!$this->checkDailyLimit()) {
                $this->addFlash('error', 'Vous avez atteint votre limite de génération pour aujourd\'hui.');
                return $this->redirectToRoute('app_pdf_index');
            }

            try {
                $pdfContent = match ($slug) {
                    'url' => $this->generateFromUrl($request),
                    'html' => $this->generateFromFile($request),
                    'wysiwyg' => $this->generateFromWysiwyg($request),
                    default => throw new \Exception('Type de génération non supporté')
                };

                $filename = 'pdf_' . time() . '.pdf';
                $filepath = $this->pdfService->savePdf($pdfContent, $filename);

                // Enregistre en BDD
                $generation = new Generation();
                $generation->setUser($this->getUser());
                $generation->setFile($filepath);
                $generation->setCreatedAt(new \DateTimeImmutable());
                $this->em->persist($generation);
                $this->em->flush();

                return $this->file(__DIR__ . '/../../public/' . $filepath);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        $template = match ($slug) {
            'url' => 'pdf/generate_url.html.twig',
            'html' => 'pdf/generate_file.html.twig',
            'wysiwyg' => 'pdf/generate_wysiwyg.html.twig',
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
}