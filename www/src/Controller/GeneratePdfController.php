<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $html = $request->request->get('html_content');

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