<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HomeController extends BaseController
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
        $user = $this->getAuthenticatedUser();

        $todayStart = new \DateTime('today');
        $todayPdfs = $this->generationRepository->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $storedPdfs = $this->generationRepository->count(['user' => $user, 'deleted' => false]);

        $totalPdfs = $this->generationRepository->count(['user' => $user]);

        $tools = $this->toolRepository->findAll();

        return $this->render('home/index.html.twig', [
            'today_pdfs' => $todayPdfs,
            'total_pdfs' => $totalPdfs,
            'stored_pdfs' => $storedPdfs,
            'tools' => $tools,
        ]);
    }
}
