<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
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

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'total_pdfs' => $totalPdfs,
        ]);
    }
}