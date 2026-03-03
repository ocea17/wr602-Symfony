<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Repository\GenerationRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class GeneratePdfController extends AbstractController
{
    private PdfGeneratorService $pdfService;
    private EntityManagerInterface $em;
    private GenerationRepository $generationRepository;

    public function __construct(
        PdfGeneratorService $pdfService,
        EntityManagerInterface $em,
        GenerationRepository $generationRepository
    ) {
        $this->pdfService = $pdfService;
        $this->em = $em;
        $this->generationRepository = $generationRepository;
    }

    private function checkDailyLimit(): bool
    {
        $user = $this->getUser();
        $plan = $user->getPlan();

        // Si le plan n'a pas de limite (PREMIUM), c'est OK
        if ($plan->getLimitGeneration() === null) {
            return true;
        }

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

        // Vérifie si la limite est dépassée
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

        return $this->render('pdf/index.html.twig', [
            'today_pdfs' => $todayPdfs,
        ]);
    }

    public function generateFromUrl(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Vérifier la limite quotidienne
            if (!$this->checkDailyLimit()) {
                $this->addFlash('error', 'Vous avez atteint votre limite de génération pour aujourd\'hui. Passez à un plan supérieur pour continuer.');
                return $this->redirectToRoute('app_pdf_index');
            }

            $url = $request->request->get('url');

            try {
                $pdfContent = $this->pdfService->generatePdfFromUrl($url);
                $filename = 'pdf_' . time() . '.pdf';
                $filepath = $this->pdfService->savePdf($pdfContent, $filename);

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

        return $this->render('pdf/generate_url.html.twig');
    }

    public function generateFromFile(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Vérifier la limite quotidienne
            if (!$this->checkDailyLimit()) {
                $this->addFlash('error', 'Vous avez atteint votre limite de génération pour aujourd\'hui. Passez à un plan supérieur pour continuer.');
                return $this->redirectToRoute('app_pdf_index');
            }

            $file = $request->files->get('file');

            if ($file) {
                try {
                    $content = file_get_contents($file->getPathname());
                    $pdfContent = $this->pdfService->generateFromHtml($content);
                    $filename = 'pdf_' . time() . '.pdf';
                    $filepath = $this->pdfService->savePdf($pdfContent, $filename);

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
        }

        return $this->render('pdf/generate_file.html.twig');
    }

    public function generateFromWysiwyg(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Vérifier la limite quotidienne
            if (!$this->checkDailyLimit()) {
                $this->addFlash('error', 'Vous avez atteint votre limite de génération pour aujourd\'hui. Passez à un plan supérieur pour continuer.');
                return $this->redirectToRoute('app_pdf_index');
            }

            $title = $request->request->get('title');
            $content = $request->request->get('content');

            // Construire le HTML à partir des champs
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

            try {
                $pdfContent = $this->pdfService->generateFromHtml($html);
                $filename = 'pdf_' . time() . '.pdf';
                $filepath = $this->pdfService->savePdf($pdfContent, $filename);

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

        return $this->render('pdf/generate_wysiwyg.html.twig');
    }
}