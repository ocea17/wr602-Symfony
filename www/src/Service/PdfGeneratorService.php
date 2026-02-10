<?php


namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfGeneratorService
{
    private string $gotenbergUrl;

    public function __construct(
        private HttpClientInterface $httpClient
    )
    {
        $this->gotenbergUrl = $_ENV['GOTENBERG_URL'] ?? 'http://gotenberg:3000';
    }

    public function generateFromHtml(string $htmlContent): string
    {

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