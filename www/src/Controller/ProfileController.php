<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    private GenerationRepository $generationRepository;
    private EntityManagerInterface $em;

    public function __construct(
        GenerationRepository $generationRepository,
        EntityManagerInterface $em
    ) {
        $this->generationRepository = $generationRepository;
        $this->em = $em;
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

    public function updateColor(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $color = $request->request->get('color');

            $user = $this->getUser();
            $user->setAvatarColor($color);

            $this->em->flush();

            $this->addFlash('success', 'Couleur modifiée avec succès !');
        }

        return $this->redirectToRoute('app_profile');
    }
}