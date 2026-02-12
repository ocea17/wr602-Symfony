<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    private GenerationRepository $generationRepository;

    public function __construct(GenerationRepository $generationRepository)
    {
        $this->generationRepository = $generationRepository;
    }

    public function index(): Response
    {
        $user = $this->getUser();

        $totalPdfs = $this->generationRepository->count(['user' => $user]);

        $todayStart = new \DateTime('today');
        $todayPdfs = $this->generationRepository->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $recentGenerations = $this->generationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            3
        );

        return $this->render('home/index.html.twig', [
            'total_pdfs' => $totalPdfs,
            'today_pdfs' => $todayPdfs,
            'recent_generations' => $recentGenerations,
        ]);
    }
}