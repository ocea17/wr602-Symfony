<?php

namespace App\Service;

use setasign\Fpdi\Fpdi;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
            'url' => $url,
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl.'/forms/chromium/convert/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function generateFromHtml(string $htmlContent): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'html_').'.html';
        file_put_contents($tmpFile, $htmlContent);

        $formData = new FormDataPart([
            'files' => DataPart::fromPath($tmpFile, 'index.html', 'text/html'),
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl.'/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        $content = $response->getContent();
        unlink($tmpFile);

        return $content;
    }

    public function savePdf(string $pdfContent, string $filename): string
    {
        $directory = 'uploads/pdfs/';
        $fullPath = __DIR__.'/../../public/'.$directory;

        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        file_put_contents($fullPath.$filename, $pdfContent);

        return $directory.$filename;
    }

    public function mergePdfs(array $files): string
    {
        $url = $this->gotenbergUrl.'/forms/pdfengines/merge';

        // Crée un boundary pour multipart
        $boundary = '----WebKitFormBoundary'.uniqid();
        $body = '';

        // Ajoute chaque fichier
        foreach ($files as $index => $file) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"files\"; filename=\"file{$index}.pdf\"\r\n";
            $body .= "Content-Type: application/pdf\r\n\r\n";
            $body .= file_get_contents($file->getPathname());
            $body .= "\r\n";
        }

        $body .= "--{$boundary}--\r\n";

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'multipart/form-data; boundary='.$boundary,
            ],
            'body' => $body,
        ]);

        return $response->getContent();
    }

    /**
     * Sépare un PDF (extrait une plage de pages).
     */
    public function splitPdf($file, int $startPage, int $endPage): string
    {
        // Vérifie que le fichier existe et est valide
        if (! $file || ! $file->isValid()) {
            throw new \Exception('Fichier invalide');
        }

        $inputPath = $file->getRealPath();

        if (! $inputPath || ! file_exists($inputPath)) {
            throw new \Exception('Fichier introuvable');
        }

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($inputPath);

            // Vérifie que les pages demandées existent
            if ($endPage > $pageCount) {
                throw new \Exception("Le PDF ne contient que {$pageCount} page(s)");
            }

            if ($startPage < 1 || $startPage > $pageCount) {
                throw new \Exception('Page de début invalide');
            }

            // Importe les pages demandées
            for ($i = $startPage; $i <= $endPage; ++$i) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            // Sauvegarde dans un fichier temporaire puis récupère le contenu
            $tempFile = sys_get_temp_dir().'/split_'.uniqid().'.pdf';
            $pdf->Output('F', $tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return $content;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la séparation : '.$e->getMessage());
        }
    }

    public function generateFromImage(string $imagePath): string
    {
        $url = $this->gotenbergUrl . '/forms/chromium/convert/html';
        
        // Lit le contenu de l'image
        $imageContent = file_get_contents($imagePath);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        
        // Crée un HTML qui affiche l'image en base64
        $base64Image = base64_encode($imageContent);
        
        // Détermine le type MIME
        $mimeType = match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg'
        };
        
        // HTML avec image en base64 (pas besoin d'envoyer le fichier séparément)
        $html = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                margin: 0; 
                padding: 0; 
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            img { 
                max-width: 100%; 
                max-height: 100vh;
                height: auto; 
            }
        </style>
    </head>
    <body>
        <img src="data:' . $mimeType . ';base64,' . $base64Image . '" alt="Image convertie en PDF" />
    </body>
    </html>';

        $response = $this->httpClient->request('POST', $url, [
            'body' => [
                'index.html' => $html,
            ],
        ]);

        return $response->getContent();
    }

    public function generateFromOffice(string $filePath): ?string
    {
        $boundary = uniqid();
        $filename = basename($filePath);
        $fileContent = file_get_contents($filePath);

        // Détecte le MIME type
        $mimeType = match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            default => 'application/octet-stream',
        };

        $body = "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"files\"; filename=\"$filename\"\r\n";
        $body .= "Content-Type: $mimeType\r\n\r\n";
        $body .= $fileContent."\r\n";
        $body .= "--$boundary--\r\n";

        $response = $this->httpClient->request('POST', $this->gotenbergUrl.'/forms/libreoffice/convert', [
            'headers' => [
                'Content-Type' => "multipart/form-data; boundary=$boundary",
            ],
            'body' => $body,
        ]);

        if (200 === $response->getStatusCode()) {
            $content = $response->getContent();

            return $content;
        }

        return null;
    }

    public function generateFromText(string $filePath): ?string
    {
        $textContent = file_get_contents($filePath);
        $filename = basename($filePath, '.txt');

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>'.htmlspecialchars($filename).'</title>
    <style>
        body {
            font-family: "Courier New", monospace;
            margin: 40px;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        h1 {
            color: #3B82F6;
            font-family: Arial, sans-serif;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div>'.htmlspecialchars($textContent).'</div>
</body>
</html>';

        return $this->generateFromHtml($html);
    }
}
