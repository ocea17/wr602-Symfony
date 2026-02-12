<?php

namespace App\Controller;

use App\Entity\Generation;
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

    public function __construct(PdfGeneratorService $pdfService, EntityManagerInterface $em)
    {
        $this->pdfService = $pdfService;
        $this->em = $em;
    }

    public function index(): Response
    {
        return $this->render('pdf/index.html.twig');
    }

    public function generateFromUrl(Request $request): Response
    {
        if ($request->isMethod('POST')) {
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