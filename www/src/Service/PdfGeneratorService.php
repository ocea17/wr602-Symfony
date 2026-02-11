<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class PdfGeneratorService
{
    private HttpClientInterface $httpClient;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = $_ENV['GOTENBERG_URL'] ?? 'http://gotenberg:3000';
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function generatePdfFromUrl(string $url): string
    {
        $formData = new FormDataPart([
            'url' => $url
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable()
        ]);

        return $response->getContent();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function generateFromHtml(string $htmlContent): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'html_') . '.html';
        file_put_contents($tmpFile, $htmlContent);

        $formData = new FormDataPart([
            'files' => DataPart::fromPath($tmpFile, 'index.html', 'text/html')
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable()
        ]);

        $content = $response->getContent();
        unlink($tmpFile);

        return $content;
    }

    public function savePdf(string $pdfContent, string $filename): string
    {
        $directory = 'uploads/pdfs/';
        $fullPath = __DIR__ . '/../../public/' . $directory;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        file_put_contents($fullPath . $filename, $pdfContent);
        return $directory . $filename;
    }
}