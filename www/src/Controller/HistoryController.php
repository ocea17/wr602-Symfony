<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HistoryController extends AbstractController
{
    private GenerationRepository $generationRepository;

    public function __construct(GenerationRepository $generationRepository)
    {
        $this->generationRepository = $generationRepository;
    }

    public function index(): Response
    {
        $user = $this->getUser();

        if ($user->getPlan()->getName() === 'FREE') {
            $this->addFlash('error', 'L\'historique est disponible à partir du plan BASIC. Améliorez votre plan pour y accéder.');
            return $this->redirectToRoute('app_subscription_change');
        }

        $generations = $this->generationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
        ]);
    }

    public function download(int $id): Response
    {
        $generation = $this->generationRepository->find($id);

        if (!$generation) {
            throw $this->createNotFoundException('PDF non trouvé');
        }

        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce PDF');
        }

        $filepath = __DIR__ . '/../../public/' . $generation->getFile();

        if (!file_exists($filepath)) {
            $this->addFlash('error', 'Le fichier PDF n\'existe plus sur le serveur');
            return $this->redirectToRoute('app_history');
        }

        return $this->file($filepath);
    }
}