<?php

namespace App\Service;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfGeneratorService
{
    private string $gotenbergUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
        $this->gotenbergUrl = $_ENV['GOTENBERG_URL'] ?? 'http://gotenberg:3000';
    }

    /**
     * Génère un PDF à partir d'une URL
     *
     * @param string $url L'URL de la page à convertir en PDF
     * @return string Le contenu du PDF généré
     * @throws TransportExceptionInterface
     * @throws RuntimeException
     */
    public function generatePdf(string $url): string
    {
        // Récupérer le contenu HTML de l'URL
        $htmlContent = file_get_contents($url);

        // Si l'URL ne peut pas être récupérée
        if ($htmlContent === false) {
            throw new RuntimeException("Impossible de récupérer le contenu de l'URL : $url");
        }


        return $this->generateFromHtml($htmlContent);
    }

    /**
     * Génère un PDF à partir de contenu HTML
     *
     * @param string $htmlContent Le contenu HTML à convertir
     * @return string Le contenu du PDF généré
     * @throws TransportExceptionInterface
     */
    public function generateFromHtml(string $htmlContent): string
    {
        // Créer un fichier temporaire avec le contenu HTML
        $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        file_put_contents($tmpFile, $htmlContent);

        try {

            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data'
                ],
                'body' => [
                    'files' => fopen($tmpFile, 'r')
                ]
            ]);


            return $response->getContent();
        } finally {

            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}