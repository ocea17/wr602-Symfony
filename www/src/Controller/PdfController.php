<?php

namespace App\Controller;

use App\Service\PdfGeneratorService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeneratePdfController extends AbstractController
{
    private PdfGeneratorService $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/pdf/generate', name: 'app_pdf_generate', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request): Response
    {
        // Créer le formulaire
        $form = $this->createFormBuilder()
            ->add('url', null, ['required' => true, 'label' => 'URL du site'])
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Récupérer l'URL saisie
                $url = $form->getData()['url'];

                // Générer le PDF via le service
                $pdfContent = $this->pdfService->generatePdf($url);

                // Retourner le PDF directement au navigateur
                return new Response(
                    $pdfContent,
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="generated.pdf"'
                    ]
                );
            } catch (Exception $e) {
                // En cas d'erreur, afficher un message
                $this->addFlash('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            }
        }

        // Afficher le formulaire
        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}