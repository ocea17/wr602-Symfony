<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    private GenerationRepository $generationRepository;
    private ToolRepository $toolRepository;

    public function __construct(
        GenerationRepository $generationRepository,
        ToolRepository $toolRepository
    ) {
        $this->generationRepository = $generationRepository;
        $this->toolRepository = $toolRepository;
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

        // Compte le total de PDFs générés
        $totalPdfs = $this->generationRepository->count(['user' => $user]);

        // Récupère tous les tools actifs
        $allTools = $this->toolRepository->findAllActive();

        return $this->render('home/index.html.twig', [
            'today_pdfs' => $todayPdfs,
            'total_pdfs' => $totalPdfs,
            'tools' => $allTools,
        ]);
    }
}