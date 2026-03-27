<?php

namespace App\Command;

use App\Entity\PdfQueue;
use App\Repository\PdfQueueRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:handle-queue',
    description: 'Process pending PDF generation queue',
)]
class HandleQueueCommand extends Command
{
    public function __construct(
        private PdfQueueRepository $queueRepository,
        private PdfGeneratorService $pdfService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Number of items to process', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        $pendingItems = $this->queueRepository->findPendingQueue($limit);

        if (empty($pendingItems)) {
            $io->info('No pending items in queue.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('Processing %d items...', count($pendingItems)));

        $processed = 0;
        $failed = 0;

        foreach ($pendingItems as $item) {
            try {
                $io->text(sprintf('Processing queue item #%d (type: %s)', $item->getId(), $item->getType()));

                $item->setStatus('processing');
                $this->em->flush();

                $pdfContent = $this->generatePdf($item);

                $filename = 'queue_' . $item->getId() . '_' . time() . '.pdf';
                $filepath = $this->pdfService->savePdf($pdfContent, $filename);

                $item->setStatus('completed');
                $item->setFilePath($filepath);
                $item->setProcessedAt(new \DateTime());
                $this->em->flush();

                $processed++;
                $io->success(sprintf('Queue item #%d processed successfully!', $item->getId()));
            } catch (\Exception $e) {
                $item->setStatus('failed');
                $item->setErrorMessage($e->getMessage());
                $item->setProcessedAt(new \DateTime());
                $this->em->flush();

                $failed++;
                $io->error(sprintf('Queue item #%d failed: %s', $item->getId(), $e->getMessage()));
            }
        }

        $io->success(sprintf('Processed: %d | Failed: %d', $processed, $failed));
        return Command::SUCCESS;
    }

    private function generatePdf(PdfQueue $item): string
    {
        $params = $item->getParameters();
        $type = $item->getType();

        return match ($type) {
            'url' => $this->pdfService->generatePdfFromUrl($params['url']),
            'html' => $this->pdfService->generateFromHtml($params['html_content']),
            'merge' => $this->pdfService->mergePdfs($params['files']),
            'text' => $this->pdfService->generateFromText($params['text_file']),
            'screenshot' => $this->pdfService->generateFromImage($params['image_path']),
            'office' => $this->pdfService->generateFromOffice($params['office_file']),
            'split' => $this->pdfService->splitPdf($params['file'], $params['start_page'], $params['end_page']),
            default => throw new \Exception("Unknown PDF type: $type"),
        };
    }
}